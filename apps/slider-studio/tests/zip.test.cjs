'use strict';

const assert = require('node:assert/strict');
const test = require('node:test');
const zip = require('../zip.js');
const decoder = new TextDecoder();

async function readArchive(entries) {
  const blob = await zip.create(entries);
  assert.equal(blob.type, 'application/zip');
  const bytes = new Uint8Array(await blob.arrayBuffer());
  const view = new DataView(bytes.buffer);
  const end = bytes.length - 22;
  assert.equal(view.getUint32(end, true), 0x06054b50);
  assert.equal(view.getUint16(end + 8, true), entries.length);
  assert.equal(view.getUint16(end + 10, true), entries.length);
  assert.equal(view.getUint32(end + 12, true) + view.getUint32(end + 16, true), end);
  let directory = view.getUint32(end + 16, true);
  const files = [];
  for (let i = 0; i < entries.length; i += 1) {
    assert.equal(view.getUint32(directory, true), 0x02014b50);
    assert.equal(view.getUint16(directory + 8, true), 0x0800);
    const local = view.getUint32(directory + 42, true);
    const size = view.getUint32(directory + 24, true);
    const nameLength = view.getUint16(directory + 28, true);
    const name = decoder.decode(bytes.slice(directory + 46, directory + 46 + nameLength));
    assert.equal(view.getUint32(local, true), 0x04034b50);
    assert.equal(view.getUint16(local + 6, true), 0x0800);
    assert.equal(view.getUint16(local + 8, true), 0);
    assert.equal(view.getUint32(local + 18, true), size);
    assert.equal(view.getUint32(local + 22, true), size);
    assert.equal(view.getUint16(local + 26, true), nameLength);
    assert.equal(decoder.decode(bytes.slice(local + 30, local + 30 + nameLength)), name);
    assert.equal(view.getUint32(local + 14, true), view.getUint32(directory + 16, true));
    const fileStart = local + 30 + nameLength;
    files.push({ name, size, crc: view.getUint32(local + 14, true), bytes: bytes.slice(fileStart, fileStart + size) });
    directory += 46 + nameLength;
  }
  assert.equal(directory, end);
  return files;
}

test('stored ZIP has correct local headers, directory offsets and known CRC-32', async () => {
  const files = await readArchive([
    { name: 'first.txt', data: '123456789' },
    { name: 'binary.png', data: new Uint8Array([0, 255, 3, 128]) },
    { name: 'blob.jpg', data: new Blob(['otro archivo']) }
  ]);
  assert.equal(files[0].crc, 0xcbf43926);
  assert.equal(decoder.decode(files[0].bytes), '123456789');
  assert.deepEqual(files[1].bytes, new Uint8Array([0, 255, 3, 128]));
  assert.equal(decoder.decode(files[2].bytes), 'otro archivo');
});

test('Unicode filenames use their UTF-8 byte lengths and NFC normalization', async () => {
  const files = await readArchive([{ name: 'ima\u0301genes/niño-🌄.webp', data: 'datos' }]);
  assert.equal(files[0].name, 'imágenes/niño-🌄.webp');
  assert.equal(files[0].size, 5);
});

test('empty archive and empty files have valid structure', async () => {
  assert.deepEqual(await readArchive([]), []);
  const files = await readArchive([{ name: 'empty.txt', data: '' }]);
  assert.equal(files[0].crc, 0);
  assert.equal(files[0].size, 0);
});

test('rejects unsafe paths, duplicates, unsupported data and excess files', async () => {
  for (const name of ['../evil.jpg', '/evil.jpg', 'C:evil.jpg', 'a\\b.jpg', 'a/../b.jpg', './a.jpg', 'a//b.jpg', 'a\0.jpg', '']) {
    await assert.rejects(zip.create([{ name, data: '' }]), Error, name);
  }
  await assert.rejects(zip.create([{ name: 'é.jpg', data: '' }, { name: 'e\u0301.jpg', data: '' }]), /repetidos/);
  await assert.rejects(zip.create([{ name: 'image.jpg', data: {} }]), /datos/);
  await assert.rejects(zip.create(new Array(65536)), /65.535/);
  await assert.rejects(zip.create(null), /lista/);
});

test('sanitizes download base names', () => {
  assert.equal(zip.sanitizeBase('../Una foto (1).jpg'), 'Una-foto-(1)');
  assert.equal(zip.sanitizeBase('a\\b?.png'), 'b');
  assert.equal(zip.sanitizeBase('.jpg'), 'imagen');
  assert.equal(zip.sanitizeBase('niño.webp'), 'niño');
});
