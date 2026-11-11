import assert from 'node:assert/strict';
import test from 'node:test';
import { reconcilePreviewConfiguration } from '../../resources/js/sliding-door-selection.js';
import { createPreviewScene } from '../../resources/js/sliding-door-preview.js';
import slidingDoorBuilder from '../../resources/js/sliding-door-builder.js';

function createBuilder() {
    return slidingDoorBuilder({
        initialStep: 1,
        payload: {
            catalog: { models: [], infills: [] },
            metadata: {
                frameColors: [{ color: 'black', label: 'Black', visual: { fill: '#202020' } }],
                infillVisuals: [{ infillId: 7, visual: { fill: '#AABBCC' } }],
                neutralVisual: { fill: '#B8B8B8' },
            },
        },
    });
}

test('frame visuals use the mapping or neutral fallback', () => {
    const builder = createBuilder();

    assert.deepEqual(builder.frameVisual('black'), { fill: '#202020' });
    assert.deepEqual(builder.frameVisual('unknown'), { fill: '#B8B8B8' });
    assert.deepEqual(builder.frameVisual(null), { fill: '#B8B8B8' });
});

test('infill visuals use numeric catalog IDs and a neutral fallback', () => {
    const builder = createBuilder();

    assert.deepEqual(builder.infillVisual(7), { fill: '#AABBCC' });
    assert.deepEqual(builder.infillVisual(8), { fill: '#B8B8B8' });
    assert.deepEqual(builder.infillVisual(null), { fill: '#B8B8B8' });
});

test('visual resolution does not add products or change selections', () => {
    const builder = createBuilder();
    const before = JSON.stringify(builder);

    builder.frameVisual('unknown');
    builder.infillVisual(8);

    assert.equal(JSON.stringify(builder), before);
});


test('shared preset choices update both door divisions and preserve unrelated configuration', () => {
    const builder = createBuilder();
    builder.metadata.layoutPresets = [
        { id: 'full_panel', panelWeights: [1] },
        { id: 'two_horizontal_panels', panelWeights: [1, 1] },
        { id: 'three_horizontal_panels', panelWeights: [1, 1, 1] },
    ];
    let id = 0;
    const normalize = () => {
        builder.previewConfiguration = reconcilePreviewConfiguration(builder.previewConfiguration, null, () => `preset-${++id}`);
    };
    normalize();
    const doorIds = [...builder.previewConfiguration.doorIds];
    for (const preset of builder.metadata.layoutPresets) {
        builder.selectLayout(preset.id);
        normalize();
        const config = builder.previewConfiguration;
        const scene = createPreviewScene(config, builder.metadata, { width: 600, height: 400 });
        assert.equal(builder.selectedLayoutId, preset.id);
        assert.deepEqual(config.doorIds, doorIds);
        assert.equal(config.openingWidth, 3000);
        assert.equal(config.openingHeight, 2400);
        assert.equal(config.frameColor, 'black');
        assert.deepEqual(scene.doors.map(door => door.panels.length), [preset.panelWeights.length, preset.panelWeights.length]);
        assert.deepEqual(scene.doors[0].panels.map(panel => panel.y), scene.doors[1].panels.map(panel => panel.y));
        const before = builder.previewConfiguration;
        builder.selectLayout(preset.id);
        assert.equal(builder.previewConfiguration, before);
    }
    assert.deepEqual(builder.metadata.layoutPresets.map(preset => preset.panelWeights), [[1], [1, 1], [1, 1, 1]]);
    const before = builder.previewConfiguration;
    builder.selectLayout('missing');
    assert.equal(builder.previewConfiguration, before);
    builder.previewConfiguration = null;
    builder.selectLayout('full_panel');
    assert.equal(builder.previewConfiguration, null);
    assert.equal(builder.selectedLayoutId, null);
});

function editingBuilder() {
    const builder = createBuilder();
    builder.catalog.infills = [{ id: 7, code: 'A', description: 'Mapped' }, { id: 8, code: 'B', description: 'Neutral' }];
    builder.metadata.layoutPresets = [
        { id: 'full_panel', panelWeights: [1] },
        { id: 'two_horizontal_panels', panelWeights: [1, 1] },
        { id: 'three_horizontal_panels', panelWeights: [1, 1, 1] },
    ];
    let id = 0;
    builder.previewConfiguration = reconcilePreviewConfiguration(builder.previewConfiguration, null, () => `edit-${++id}`);
    builder.previewDoors = builder.previewConfiguration.doorIds.map((doorId, index) => ({
        id: doorId, index,
        panels: builder.previewConfiguration.panelIdsByDoor[index].map((panelId, panelIndex) => ({ id: panelId, index: panelIndex })),
    }));
    builder.selectPreviewPanel(builder.previewDoors[0].id, builder.previewDoors[0].panels[0].id);
    return builder;
}

test('palette accepts catalog finishes for a selected panel and rejects unknown finishes', () => {
    const builder = editingBuilder();
    builder.chooseInfill(8);
    assert.equal(builder.paletteInfillId, 8);
    assert.deepEqual(builder.infillVisual(8), builder.metadata.neutralVisual);
    builder.chooseInfill(999);
    assert.equal(builder.paletteInfillId, 8);
    builder.chooseInfill(null);
    assert.equal(builder.paletteInfillId, null);
    builder.clearPreviewSelection();
    builder.chooseInfill(7);
    assert.equal(builder.paletteInfillId, null);
});
