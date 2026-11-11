import assert from 'node:assert/strict';
import test from 'node:test';
import { createPreviewScene } from '../../resources/js/sliding-door-preview.js';

function metadata() {
    return {
        frameColors: [{ color: 'black', label: 'Black', visual: { fill: '#202020' } }],
        infillVisuals: [{ infillId: 7, visual: { fill: '#AABBCC' } }],
        neutralVisual: { fill: '#B8B8B8' },
    };
}

function configuration() {
    return {
        openingWidth: 3000,
        openingHeight: 2400,
        doorCount: 3,
        panelWeightsByDoor: [[1], [1, 1], [1, 1, 1]],
        frameColor: 'black',
        infillIdsByDoor: [[7], [7, null], [null, 7, 999]],
    };
}

test('preview scenes preserve geometry and resolve fills per panel', () => {
    const scene = createPreviewScene(configuration(), metadata(), { width: 800, height: 500 });

    assert.equal(scene.doors.length, 3);
    assert.deepEqual(scene.doors.map((door) => door.panels.length), [1, 2, 3]);
    assert.equal(scene.frameFill, '#202020');
    assert.deepEqual(scene.doors.map((door) => door.panels.map((panel) => panel.fill)), [
        ['#AABBCC'], ['#AABBCC', '#B8B8B8'], ['#B8B8B8', '#AABBCC', '#B8B8B8'],
    ]);
    assert.equal(scene.opening.width / scene.opening.height, 3000 / 2400);
    assert.ok(scene.opening.x >= 48 && scene.opening.y >= 48);
    assert.ok(scene.opening.x + scene.opening.width <= 752);
    assert.ok(scene.opening.y + scene.opening.height <= 452);
});

test('unknown frame colours and unselected infills use neutral visuals', () => {
    const input = configuration();
    delete input.frameColor;
    delete input.infillIdsByDoor;
    const scene = createPreviewScene(input, metadata(), { width: 320, height: 300 });

    assert.equal(scene.frameFill, '#B8B8B8');
    for (const door of scene.doors) {
        door.panels.forEach((panel) => assert.equal(panel.fill, '#B8B8B8'));
    }
});

test('visual frame widths shrink for thin doors without altering opening proportions', () => {
    const input = { ...configuration(), openingWidth: 1, openingHeight: 1000000 };
    const scene = createPreviewScene(input, metadata(), { width: 320, height: 300 });

    for (const door of scene.doors) {
        assert.ok(door.frameWidth > 0 && door.frameWidth <= door.width / 12);
    }
    assert.ok(Math.abs(scene.opening.width / scene.opening.height - 1e-6) < 1e-18);
});

test('scene generation never modifies configuration or catalog metadata', () => {
    const input = configuration();
    const visuals = metadata();
    const before = structuredClone({ input, visuals });
    createPreviewScene(input, visuals, { width: 800, height: 500 });

    assert.deepEqual({ input, visuals }, before);
});

test('incomplete geometry does not produce a fabricated preview', () => {
    const input = configuration();
    input.openingWidth = 0;
    assert.throws(() => createPreviewScene(input, metadata(), { width: 800, height: 500 }), RangeError);
});
