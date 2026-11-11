import assert from 'node:assert/strict';
import test from 'node:test';
import { reconcilePreviewConfiguration, reconcilePreviewSelection, previewSelectionTargets } from '../../resources/js/sliding-door-selection.js';
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


test('finish edits update only the selected panel and render mapped or neutral fills immediately', () => {
    const builder = editingBuilder();
    const original = structuredClone(builder.previewConfiguration);
    builder.chooseInfill(7);
    assert.deepEqual(builder.previewConfiguration.infillIdsByDoor, [[7, null, null], [null, null, null]]);
    let scene = createPreviewScene(builder.previewConfiguration, builder.metadata, { width: 600, height: 400 });
    assert.equal(scene.doors[0].panels[0].fill, '#AABBCC');
    assert.equal(scene.doors[1].panels[0].fill, '#B8B8B8');
    builder.selectPreviewPanel(builder.previewDoors[1].id, builder.previewDoors[1].panels[2].id);
    assert.equal(builder.paletteInfillId, null);
    builder.chooseInfill(8);
    assert.deepEqual(builder.previewConfiguration.infillIdsByDoor, [[7, null, null], [null, null, 8]]);
    builder.selectPreviewPanel(builder.previewDoors[0].id, builder.previewDoors[0].panels[0].id);
    assert.equal(builder.paletteInfillId, 7);
    builder.chooseInfill(null);
    scene = createPreviewScene(builder.previewConfiguration, builder.metadata, { width: 600, height: 400 });
    assert.equal(scene.doors[0].panels[0].fill, '#B8B8B8');
    assert.equal(builder.previewConfiguration.openingWidth, original.openingWidth);
    assert.deepEqual(builder.previewConfiguration.doorIds, original.doorIds);
    assert.deepEqual(builder.previewConfiguration.panelIdsByDoor, original.panelIdsByDoor);
});


test('bulk finish and door copy actions keep independent arrays and stable identities', () => {
    const builder = editingBuilder();
    const ids = structuredClone(builder.previewConfiguration.panelIdsByDoor);
    builder.chooseInfill(7);
    builder.applyFinishToAllDoors();
    assert.deepEqual(builder.previewConfiguration.infillIdsByDoor, [[7,7,7], [7,7,7]]);
    builder.selectPreviewPanel(builder.previewDoors[0].id, builder.previewDoors[0].panels[1].id);
    builder.chooseInfill(8);
    builder.copySelectedDoor();
    assert.deepEqual(builder.previewConfiguration.infillIdsByDoor, [[7,8,7], [7,8,7]]);
    assert.notEqual(builder.previewConfiguration.infillIdsByDoor[0], builder.previewConfiguration.infillIdsByDoor[1]);
    builder.chooseInfill(null);
    assert.deepEqual(builder.previewConfiguration.infillIdsByDoor, [[7,null,7], [7,8,7]]);
    assert.deepEqual(builder.previewConfiguration.panelIdsByDoor, ids);
    builder.clearPreviewSelection();
    const before = builder.previewConfiguration;
    builder.copySelectedDoor();
    builder.applyFinishToAllDoors();
    assert.equal(builder.previewConfiguration, before);
});


test('layout remapping preserves corresponding finishes and never resurrects removed panels', () => {
    const builder = editingBuilder();
    builder.previewConfiguration.infillIdsByDoor = [[7,8,7], [8,7,8]];
    const original = structuredClone(builder.previewConfiguration);
    builder.selectPreviewPanel(builder.previewDoors[1].id, builder.previewDoors[1].panels[2].id);
    builder.selectLayout('two_horizontal_panels');
    let config = reconcilePreviewConfiguration(builder.previewConfiguration, original, () => 'unused');
    assert.deepEqual(config.infillIdsByDoor, [[7,8], [8,7]]);
    assert.deepEqual(config.panelIdsByDoor, original.panelIdsByDoor.map(ids => ids.slice(0,2)));
    const selection = reconcilePreviewSelection(builder.previewSelection, previewSelectionTargets(config));
    assert.deepEqual(selection, { doorId: original.doorIds[1], panelId: null });
    builder.previewConfiguration = config;
    builder.selectLayout('full_panel');
    config = reconcilePreviewConfiguration(builder.previewConfiguration, config, () => 'unused');
    builder.previewConfiguration = config;
    builder.selectLayout('three_horizontal_panels');
    let id = 0;
    const expanded = reconcilePreviewConfiguration(builder.previewConfiguration, config, () => `new-${++id}`);
    assert.deepEqual(expanded.infillIdsByDoor, [[7,null,null], [8,null,null]]);
    assert.deepEqual(expanded.doorIds, original.doorIds);
    for (let index = 0; index < 2; index++) {
        assert.equal(expanded.panelIdsByDoor[index][0], original.panelIdsByDoor[index][0]);
        assert.ok(!original.panelIdsByDoor.flat().includes(expanded.panelIdsByDoor[index][1]));
        assert.ok(!original.panelIdsByDoor.flat().includes(expanded.panelIdsByDoor[index][2]));
    }
});
