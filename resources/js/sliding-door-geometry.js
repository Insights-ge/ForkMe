/**
 * @typedef {{ x: number, y: number, width: number, height: number }} Rectangle
 * @typedef {Rectangle & { index: number }} PanelGeometry
 * @typedef {Rectangle & { index: number, panels: PanelGeometry[] }} DoorGeometry
 */

/**
 * Calculate a front elevation in normalized coordinates (opening height = 1).
 * Panel weights describe relative heights from top to bottom for each door.
 * All rectangles use the opening's coordinate system. No track overlaps,
 * profile deductions, viewport scaling or manufacturing sizes are inferred.
 *
 * @param {{ openingWidth: number, openingHeight: number, doorCount: number, panelWeightsByDoor: number[][] }} configuration
 * @returns {{ opening: Rectangle, doors: DoorGeometry[] }}
 * @throws {RangeError} When dimensions, counts or weights cannot form nonzero finite rectangles.
 */
export function calculateDoorGeometry({ openingWidth, openingHeight, doorCount, panelWeightsByDoor }) {
    requirePositiveFinite(openingWidth);
    requirePositiveFinite(openingHeight);

    if (!Number.isSafeInteger(doorCount) || doorCount < 1) {
        throw new RangeError('Door count must be a positive safe integer.');
    }

    if (!Array.isArray(panelWeightsByDoor) || panelWeightsByDoor.length !== doorCount) {
        throw new RangeError('Panel weights must be supplied for every door.');
    }

    const width = openingWidth / openingHeight;
    requirePositiveFinite(width);

    return {
        opening: { x: 0, y: 0, width, height: 1 },
        doors: Array.from(panelWeightsByDoor, (weights, index) => calculateDoor(index, doorCount, width, weights)),
    };
}

function calculateDoor(index, count, openingWidth, weights) {
    const x = openingWidth * (index / count);
    const right = openingWidth * ((index + 1) / count);
    const width = right - x;
    requirePositiveFinite(width);

    return {
        index,
        x,
        y: 0,
        width,
        height: 1,
        panels: calculatePanels(x, width, weights),
    };
}

function calculatePanels(x, width, weights) {
    if (!Array.isArray(weights) || weights.length === 0) {
        throw new RangeError('Each door must contain at least one panel weight.');
    }

    const values = Array.from(weights);
    values.forEach(requirePositiveFinite);

    // Scaling first avoids overflowing the sum of otherwise valid weights.
    const largest = values.reduce((maximum, value) => Math.max(maximum, value), 0);
    const scaled = values.map((value) => value / largest);
    const total = scaled.reduce((sum, value) => sum + value, 0);
    let cumulative = 0;
    let y = 0;

    return scaled.map((weight, index) => {
        cumulative += weight;
        const bottom = index === scaled.length - 1 ? 1 : cumulative / total;
        const height = bottom - y;
        requirePositiveFinite(height);

        const panel = { index, x, y, width, height };
        y = bottom;

        return panel;
    });
}

function requirePositiveFinite(value) {
    if (!Number.isFinite(value) || value <= 0) {
        throw new RangeError('Geometry requires positive finite dimensions and proportions.');
    }
}
