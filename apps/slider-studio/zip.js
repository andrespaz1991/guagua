(function (root, factory) {
  'use strict';
  const api = factory();
  if (typeof module === 'object' && module.exports) module.exports = api;
  else root.SliderZip = api;
})(typeof globalThis !== 'undefined' ? globalThis : this, function () {
  'use strict';

  const encoder = new TextEncoder();
  const MAX_U32 = 0xfffffffe;
  const table = new Uint32Array(256);
  for (let n = 0; n < 256; n += 1) {
    let value = n;
    for (let bit = 0; bit < 8; bit += 1) {
      value = (value >>> 1) ^ ((value & 1) ? 0xedb88320 : 0);
    }
    table[n] = value >>> 0;
  }

  function updateCrc(crc, bytes) {
    for (let i = 0; i < bytes.length; i += 1) {
      crc = table[(crc ^ bytes[i]) & 255] ^ (crc >>> 8);
    }
    return crc;
  }

  async function crc32(data) {
    let crc = 0xffffffff;
    if (data instanceof Uint8Array) {
      crc = updateCrc(crc, data);
    } else if (typeof data.stream === 'function') {
      const reader = data.stream().getReader();
      try {
        while (true) {
          const chunk = await reader.read();
          if (chunk.done) break;
          crc = updateCrc(crc, chunk.value);
        }
      } finally {
        reader.releaseLock();
      }
    } else {
      crc = updateCrc(crc, new Uint8Array(await data.arrayBuffer()));
    }
    return (crc ^ 0xffffffff) >>> 0;
  }

  function zipDate(now) {
    const year = Math.min(2107, Math.max(1980, now.getFullYear()));
    return {
      time: (now.getHours() << 11) | (now.getMinutes() << 5) | (now.getSeconds() >>> 1),
      date: ((year - 1980) << 9) | ((now.getMonth() + 1) << 5) | now.getDate()
    };
  }

  function validateName(value) {
    if (typeof value !== 'string' || !value.trim()) {
      throw new Error('Cada archivo del ZIP necesita un nombre.');
    }
    const name = value.normalize('NFC');
    if (/^[\/\\]|^[a-z]:|[\\\x00-\x1f\x7f]/i.test(name) ||
        name.split('/').some(part => !part || part === '.' || part === '..')) {
      throw new Error('El nombre del archivo contiene una ruta no válida.');
    }
    const bytes = encoder.encode(name);
    if (bytes.length > 65535) throw new Error('El nombre del archivo es demasiado largo para un ZIP.');
    return { name, bytes };
  }

  function sanitizeBase(value) {
    const base = String(value || 'imagen').split(/[\/\\]/).pop()
      .replace(/\.[^.]*$/, '').normalize('NFC')
      .replace(/[<>:"\/\\|?*\x00-\x1f\x7f]/g, '-')
      .replace(/\s+/g, '-').replace(/^[.\s-]+|[.\s-]+$/g, '')
      .slice(0, 120);
    return base || 'imagen';
  }

  /** Create an uncompressed, UTF-8 ZIP without uploading or external libraries. */
  async function create(entries) {
    if (!Array.isArray(entries)) throw new TypeError('La lista de archivos del ZIP no es válida.');
    if (entries.length > 65535) throw new Error('El ZIP admite hasta 65.535 archivos.');

    const names = new Set();
    let totalSize = 22;
    const files = entries.map(entry => {
      if (!entry || typeof entry !== 'object') throw new TypeError('El archivo del ZIP no es válido.');
      const filename = validateName(entry.name);
      if (names.has(filename.name)) throw new Error('Hay nombres de archivo repetidos en el ZIP.');
      names.add(filename.name);
      let data = entry.data;
      if (typeof data === 'string') data = encoder.encode(data);
      if (!(data instanceof Uint8Array) && !(data instanceof Blob)) {
        throw new TypeError('Los datos del archivo deben ser una imagen, un texto o bytes.');
      }
      const size = data instanceof Uint8Array ? data.byteLength : data.size;
      totalSize += 30 + 46 + filename.bytes.length * 2 + size;
      if (size > MAX_U32 || totalSize > MAX_U32) {
        throw new Error('El ZIP supera el límite de 4 GB. Descarga las imágenes en grupos más pequeños.');
      }
      return { ...filename, data, size };
    });

    const stamp = zipDate(new Date());
    const parts = [];
    const central = [];
    let offset = 0;
    let centralSize = 0;
    for (const file of files) {
      const checksum = await crc32(file.data);
      const local = new Uint8Array(30 + file.bytes.length);
      const lv = new DataView(local.buffer);
      lv.setUint32(0, 0x04034b50, true);
      lv.setUint16(4, 20, true);
      lv.setUint16(6, 0x0800, true);
      lv.setUint16(8, 0, true);
      lv.setUint16(10, stamp.time, true);
      lv.setUint16(12, stamp.date, true);
      lv.setUint32(14, checksum, true);
      lv.setUint32(18, file.size, true);
      lv.setUint32(22, file.size, true);
      lv.setUint16(26, file.bytes.length, true);
      lv.setUint16(28, 0, true);
      local.set(file.bytes, 30);
      parts.push(local, file.data);

      const directory = new Uint8Array(46 + file.bytes.length);
      const cv = new DataView(directory.buffer);
      cv.setUint32(0, 0x02014b50, true);
      cv.setUint16(4, 20, true);
      cv.setUint16(6, 20, true);
      cv.setUint16(8, 0x0800, true);
      cv.setUint16(10, 0, true);
      cv.setUint16(12, stamp.time, true);
      cv.setUint16(14, stamp.date, true);
      cv.setUint32(16, checksum, true);
      cv.setUint32(20, file.size, true);
      cv.setUint32(24, file.size, true);
      cv.setUint16(28, file.bytes.length, true);
      cv.setUint32(42, offset, true);
      directory.set(file.bytes, 46);
      central.push(directory);
      centralSize += directory.length;
      offset += local.length + file.size;
    }

    const end = new Uint8Array(22);
    const ev = new DataView(end.buffer);
    ev.setUint32(0, 0x06054b50, true);
    ev.setUint16(8, files.length, true);
    ev.setUint16(10, files.length, true);
    ev.setUint32(12, centralSize, true);
    ev.setUint32(16, offset, true);
    return new Blob([...parts, ...central, end], { type: 'application/zip' });
  }

  return { create, sanitizeBase };
});
