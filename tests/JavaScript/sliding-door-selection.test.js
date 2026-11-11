import assert from 'node:assert/strict';
import test from 'node:test';
import { reconcilePreviewConfiguration, previewSelectionTargets, reconcilePreviewSelection } from '../../resources/js/sliding-door-selection.js';
import slidingDoorBuilder from '../../resources/js/sliding-door-builder.js';

function configuration() {
    return {
        openingWidth: 3000, openingHeight: 2400, doorCount: 2,
        panelWeightsByDoor: [[1, 1], [1, 1, 1]],
    };
}

function identifiers() {
    let counter = 0;
    return () => `instance-${++counter}`;
}

test('instance IDs are unique and input configuration remains unchanged', () => {
    const input = configuration();
    const before = structuredClone(input);
    const normalized = reconcilePreviewConfiguration(input, null, identifiers());
    const ids = [...normalized.doorIds, ...normalized.panelIdsByDoor.flat()];

    assert.equal(new Set(ids).size, 7);
    assert.deepEqual(input, before);
    assert.deepEqual(normalized.panelWeightsByDoor, input.panelWeightsByDoor);
    assert.equal(reconcilePreviewConfiguration(normalized, normalized), normalized);
});

test('resizing and adding doors preserve existing instance identities', () => {
    const nextId = identifiers();
    const original = reconcilePreviewConfiguration(configuration(), null, nextId);
    const updated = reconcilePreviewConfiguration({
        ...configuration(), openingWidth: 4000, doorCount: 3,
        panelWeightsByDoor: [[1, 1], [1, 1, 1], [1]],
    }, original, nextId);

    assert.deepEqual(updated.doorIds.slice(0, 2), original.doorIds);
    assert.deepEqual(updated.panelIdsByDoor.slice(0, 2), original.panelIdsByDoor);
    assert.notEqual(updated.doorIds[2], original.doorIds[0]);
});

test('explicit door and panel reordering preserves selection by identity', () => {
    const original = reconcilePreviewConfiguration(configuration(), null, identifiers());
    const selected = { doorId: original.doorIds[1], panelId: original.panelIdsByDoor[1][2] };
    const reordered = reconcilePreviewConfiguration({
        ...original,
        doorIds: [...original.doorIds].reverse(),
        panelWeightsByDoor: [...original.panelWeightsByDoor].reverse(),
        panelIdsByDoor: [[...original.panelIdsByDoor[1]].reverse(), original.panelIdsByDoor[0]],
    }, original);

    assert.deepEqual(reconcilePreviewSelection(selected, previewSelectionTargets(reordered)), selected);
    assert.equal(previewSelectionTargets(reordered)[0].panels[0].id, selected.panelId);
});

test('removed doors clear selection and removed panels retain only their parent door', () => {
    const nextId = identifiers();
    const original = reconcilePreviewConfiguration(configuration(), null, nextId);
    const targets = previewSelectionTargets(original);
    const selected = { doorId: targets[1].id, panelId: targets[1].panels[2].id };

    assert.deepEqual(reconcilePreviewSelection(selected, targets.slice(0, 1)), { doorId: null, panelId: null });
    const fewerPanels = reconcilePreviewConfiguration({
        ...original, panelWeightsByDoor: [[1, 1], [1]],
    }, original, nextId);
    assert.deepEqual(reconcilePreviewSelection(selected, previewSelectionTargets(fewerPanels)), {
        doorId: selected.doorId, panelId: null,
    });
    assert.equal(reconcilePreviewConfiguration(null, original), null);
    assert.deepEqual(previewSelectionTargets(null), []);
});

test('duplicate invalid and malformed instance IDs are rejected', () => {
    for (const overrides of [
        { doorIds: ['same', 'same'] },
        { doorIds: ['', 'second'] },
        { doorIds: [1, 'second'] },
        { doorIds: 'invalid' },
        { panelIdsByDoor: 'invalid' },
        { panelIdsByDoor: ['invalid'] },
        { panelIdsByDoor: [['same', 'same']] },
    ]) {
        assert.throws(() => reconcilePreviewConfiguration({ ...configuration(), ...overrides }, null, identifiers()), RangeError);
    }
});

test('selection actions update builder state without modifying the door configuration', () => {
    const normalized = reconcilePreviewConfiguration(configuration(), null, identifiers());
    const builder = slidingDoorBuilder({ initialStep: 1, payload: { catalog: {}, metadata: {} } });
    builder.previewConfiguration = normalized;
    builder.previewDoors = previewSelectionTargets(normalized);
    const before = structuredClone(normalized);
    const door = builder.previewDoors[1];

    builder.selectPreviewPanel(door.id, door.panels[1].id);
    assert.deepEqual(builder.previewSelection, { doorId: door.id, panelId: door.panels[1].id });
    builder.selectPreviewPanel(door.id, 'missing-panel');
    assert.equal(builder.previewSelection.panelId, door.panels[1].id);
    builder.selectPreviewDoor(door.id);
    assert.deepEqual(builder.previewSelection, { doorId: door.id, panelId: null });
    builder.clearPreviewSelection();
    assert.deepEqual(builder.previewSelection, { doorId: null, panelId: null });
    assert.deepEqual(builder.previewConfiguration, before);
});
