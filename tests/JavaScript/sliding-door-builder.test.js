import assert from 'node:assert/strict';
import test from 'node:test';
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
