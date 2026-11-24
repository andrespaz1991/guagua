'use strict';

const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const test = require('node:test');

// Only the pure sizing functions run here. Rendering and image decoding need
// real browser canvases and are covered by browser QA rather than mock pixels.
const context = { window: {} };
vm.runInNewContext(fs.readFileSync(path.join(__dirname, '..', 'engine.js'), 'utf8'), context);
const engine = context.window.SliderEngine;
const approximately = (actual, expected, message) =>
  assert.ok(Math.abs(actual - expected) < 1e-7 * Math.max(1, Math.abs(expected)), message);

test('contain keeps the complete source and its original aspect at every position', () => {
  for (const [sourceWidth, sourceHeight] of [[4000, 3000], [3000, 4000], [6000, 1], [1, 6000], [1, 1]]) {
    for (const [width, height] of [[1920, 600], [64, 6000], [6000, 64], [1080, 1080]]) {
      for (const mode of ['blur', 'mirror', 'color', 'transparent']) {
        for (const positionX of [0, 0.5, 1]) {
          for (const positionY of [0, 0.5, 1]) {
            const placement = engine.getPlacement(sourceWidth, sourceHeight, { width, height, mode, positionX, positionY });
            approximately(placement.width / placement.height, sourceWidth / sourceHeight, 'source aspect retained');
            assert.ok(placement.x >= -1e-7 && placement.y >= -1e-7, 'top and left remain inside');
            assert.ok(placement.x + placement.width <= width + 1e-7, 'right remains inside');
            assert.ok(placement.y + placement.height <= height + 1e-7, 'bottom remains inside');
            assert.equal(placement.cropped, false);
          }
        }
      }
    }
  }
});

test('cover and zoom fill the frame without stretching or exposing edges', () => {
  for (const [sourceWidth, sourceHeight] of [[4000, 3000], [3000, 4000], [6000, 1], [1, 6000]]) {
    for (const [width, height] of [[1920, 600], [64, 6000], [6000, 64], [1080, 1080]]) {
      for (const positionX of [0, 0.5, 1]) {
        for (const positionY of [0, 0.5, 1]) {
          for (const zoom of [1, 1.3, 3]) {
            const placement = engine.getPlacement(sourceWidth, sourceHeight, { width, height, mode: 'cover', positionX, positionY, zoom });
            approximately(placement.width / placement.height, sourceWidth / sourceHeight, 'source aspect retained');
            assert.ok(placement.x <= 1e-7 && placement.y <= 1e-7, 'origin covers top and left');
            assert.ok(placement.x + placement.width >= width - 1e-7, 'right covers frame');
            assert.ok(placement.y + placement.height >= height - 1e-7, 'bottom covers frame');
          }
        }
      }
    }
  }
});

test('preview geometry scales proportionally to export with original logical dimensions', () => {
  // An actual 1×6000 source becomes a 1×1800 preview raster. Passing the
  // original dimensions to placement prevents this rounding changing its fit.
  for (const [sourceWidth, sourceHeight] of [[1, 6000], [6000, 1], [3911, 2733]]) {
    for (const mode of ['blur', 'mirror', 'cover']) {
      const full = engine.getPlacement(sourceWidth, sourceHeight, { width: 1920, height: 600, mode, zoom: 1.7, positionX: 0.2, positionY: 0.8 });
      const small = engine.getPlacement(sourceWidth, sourceHeight, { width: 960, height: 300, mode, zoom: 1.7, positionX: 0.2, positionY: 0.8, preview: true });
      for (const field of ['x', 'y', 'width', 'height', 'scale']) approximately(small[field], full[field] / 2, field + ' scales exactly');
    }
  }
});

test('output bounds, source bounds and slider normalization are enforced', () => {
  for (const invalid of [0, -1, NaN, Infinity, '200']) {
    assert.throws(() => engine.getPlacement(invalid, 200), /tamaño válido/);
    assert.throws(() => engine.getPlacement(200, invalid), /tamaño válido/);
  }
  assert.throws(() => engine.normalizeOptions({ width: 63, height: 600 }), /64 y 6000/);
  assert.throws(() => engine.normalizeOptions({ width: 6001, height: 600 }), /64 y 6000/);
  assert.throws(() => engine.normalizeOptions({ width: 6000, height: 6000 }), /24 megapíxeles/);
  assert.throws(() => engine.normalizeOptions({ width: NaN, height: 600 }), /64 y 6000/);
  const preview = engine.normalizeOptions({ width: 1000, height: 11, preview: true });
  assert.equal(preview.height, 11);
  const bounded = engine.normalizeOptions({ zoom: 100, positionX: -4, positionY: 10, blur: 900, mode: 'invalid', color: 'bad' });
  assert.equal(bounded.zoom, 3);
  assert.equal(bounded.positionX, 0);
  assert.equal(bounded.positionY, 1);
  assert.equal(bounded.blur, 200);
  assert.equal(bounded.mode, 'blur');
  assert.equal(bounded.color, '#f0efed');
});
