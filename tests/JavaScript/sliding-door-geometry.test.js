import assert from 'node:assert/strict';
import test from 'node:test';
import { calculateDoorGeometry } from '../../resources/js/sliding-door-geometry.js';

function approximately(actual, expected) {
    const tolerance = 16 * Number.EPSILON * Math.max(1, Math.abs(expected));
    assert.ok(Math.abs(actual - expected) <= tolerance, `${actual} differs from ${expected}`);
}

function geometry(overrides = {}) {
    return calculateDoorGeometry({
        openingWidth: 3000,
        openingHeight: 2000,
        doorCount: 3,
        panelWeightsByDoor: [[1], [1, 1], [1, 1, 1]],
        ...overrides,
    });
}

test('mixed door layouts preserve the opening ratio and horizontal divisions', () => {
    const result = geometry();

    assert.deepEqual(result.opening, { x: 0, y: 0, width: 1.5, height: 1 });
    assert.deepEqual(result.doors.map(({ x, width }) => ({ x, width })), [
        { x: 0, width: 0.5 }, { x: 0.5, width: 0.5 }, { x: 1, width: 0.5 },
    ]);
    assert.deepEqual(result.doors.map((door) => door.panels.length), [1, 2, 3]);
    assert.deepEqual(result.doors[1].panels, [
        { index: 0, x: 0.5, y: 0, width: 0.5, height: 0.5 },
        { index: 1, x: 0.5, y: 0.5, width: 0.5, height: 0.5 },
    ]);
    result.doors[2].panels.forEach((panel) => approximately(panel.height, 1 / 3));
});

test('proportions are relative and independent of measurement units', () => {
    const baseline = geometry();
    const scaled = geometry({
        openingWidth: 3,
        openingHeight: 2,
        panelWeightsByDoor: [[7], [10, 10], [100, 100, 100]],
    });
    assert.deepEqual(scaled, baseline);

    const unequal = geometry({ doorCount: 1, panelWeightsByDoor: [[1, 2, 1]] });
    assert.deepEqual(unequal.doors[0].panels.map(({ y, height }) => ({ y, height })), [
        { y: 0, height: 0.25 }, { y: 0.25, height: 0.5 }, { y: 0.75, height: 0.25 },
    ]);
});

for (const [openingWidth, openingHeight] of [[2400, 2400], [3200, 1800], [1200, 2600]]) {
    for (const doorCount of [1, 2, 3, 4]) {
        test(`${openingWidth} × ${openingHeight} with ${doorCount} doors tiles the opening without gaps`, () => {
            const result = geometry({
                openingWidth,
                openingHeight,
                doorCount,
                panelWeightsByDoor: Array.from({ length: doorCount }, () => [1, 1, 1]),
            });
            assert.equal(result.doors.length, doorCount);
            let right = 0;

            for (const door of result.doors) {
                approximately(door.x, right);
                approximately(door.width, result.opening.width / doorCount);
                right = door.x + door.width;
                let bottom = 0;
                for (const panel of door.panels) {
                    approximately(panel.y, bottom);
                    assert.equal(panel.x, door.x);
                    assert.equal(panel.width, door.width);
                    assert.ok(panel.height > 0);
                    bottom = panel.y + panel.height;
                    assert.ok(bottom <= 1 + Number.EPSILON);
                }
                approximately(bottom, 1);
            }
            approximately(right, result.opening.width);
        });
    }
}

test('calculations are deterministic and never mutate input arrays', () => {
    const panelWeightsByDoor = Object.freeze([Object.freeze([1]), Object.freeze([1, 1])]);
    const input = Object.freeze({ openingWidth: 2400, openingHeight: 2200, doorCount: 2, panelWeightsByDoor });

    const first = calculateDoorGeometry(input);
    const second = calculateDoorGeometry(input);
    assert.deepEqual(first, second);
    first.doors[0].panels[0].height = 0;
    assert.equal(second.doors[0].panels[0].height, 1);
    assert.deepEqual(input.panelWeightsByDoor, [[1], [1, 1]]);
});

test('large panel weights do not overflow during normalization', () => {
    const result = geometry({ doorCount: 1, panelWeightsByDoor: [[Number.MAX_VALUE, Number.MAX_VALUE]] });
    assert.deepEqual(result.doors[0].panels.map((panel) => panel.height), [0.5, 0.5]);
});

test('invalid dimensions, counts and panel proportions are rejected', () => {
    const invalid = [
        { openingWidth: 0 }, { openingHeight: -1 }, { openingWidth: Infinity },
        { openingHeight: NaN }, { openingWidth: '3000' },
        { openingWidth: Number.MAX_VALUE, openingHeight: Number.MIN_VALUE },
        { doorCount: 0 }, { doorCount: -1 }, { doorCount: 1.5 }, { doorCount: Infinity },
        { panelWeightsByDoor: undefined }, { panelWeightsByDoor: [[1]] },
        { panelWeightsByDoor: [[1], [], [1]] },
        { panelWeightsByDoor: [[1], [0], [1]] },
        { panelWeightsByDoor: [[1], [-1], [1]] },
        { panelWeightsByDoor: [[1], ['1'], [1]] },
        { panelWeightsByDoor: [[1], [NaN], [1]] },
        { panelWeightsByDoor: [[1], [Infinity], [1]] },
        { panelWeightsByDoor: [[1], new Array(2), [1]] },
        { panelWeightsByDoor: new Array(3) },
        { panelWeightsByDoor: [[1], [Number.MIN_VALUE, Number.MAX_VALUE], [1]] },
    ];
    for (const configuration of invalid) {
        assert.throws(() => geometry(configuration), RangeError);
    }
});
