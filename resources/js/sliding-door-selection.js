import { calculateDoorGeometry } from './sliding-door-geometry.js';

/** Add stable instance identities without changing layouts, finishes or measurements. */
export function reconcilePreviewConfiguration(configuration, previous, createId = () => globalThis.crypto.randomUUID()) {
    if (configuration === null) {
        return null;
    }

    calculateDoorGeometry(configuration);
    if ((configuration.doorIds !== undefined && !Array.isArray(configuration.doorIds))
        || (configuration.panelIdsByDoor !== undefined && !Array.isArray(configuration.panelIdsByDoor))) {
        throw new RangeError('Instance IDs must be supplied as arrays.');
    }
    const doorIds = [];
    const panelIdsByDoor = [];
    const seenDoors = new Set();
    const seenPanels = new Set();

    for (let index = 0; index < configuration.doorCount; index++) {
        const id = configuration.doorIds?.[index] ?? previous?.doorIds?.[index] ?? createId();
        requireIdentity(id, seenDoors);
        doorIds.push(id);

        const previousIndex = previous?.doorIds?.indexOf(id) ?? -1;
        if (configuration.panelIdsByDoor?.[index] !== undefined && !Array.isArray(configuration.panelIdsByDoor[index])) {
            throw new RangeError('Panel instance IDs must be supplied as arrays.');
        }
        const panelIds = configuration.panelWeightsByDoor[index].map((_, panelIndex) => {
            const panelId = configuration.panelIdsByDoor?.[index]?.[panelIndex]
                ?? previous?.panelIdsByDoor?.[previousIndex]?.[panelIndex]
                ?? createId();
            requireIdentity(panelId, seenPanels);
            return panelId;
        });
        panelIdsByDoor.push(panelIds);
    }

    if (sameIdentities(configuration, doorIds, panelIdsByDoor)) {
        return configuration;
    }

    return { ...configuration, doorIds, panelIdsByDoor };
}

export function previewSelectionTargets(configuration) {
    return configuration?.doorIds.map((id, index) => ({
        id,
        index,
        panels: configuration.panelIdsByDoor[index].map((panelId, panelIndex) => ({ id: panelId, index: panelIndex })),
    })) ?? [];
}

export function reconcilePreviewSelection(selection, doors) {
    const door = doors.find((candidate) => candidate.id === selection.doorId);
    if (!door) {
        return { doorId: null, panelId: null };
    }
    if (selection.panelId !== null && !door.panels.some((panel) => panel.id === selection.panelId)) {
        return { doorId: door.id, panelId: null };
    }
    return selection;
}

function requireIdentity(id, seen) {
    if (typeof id !== 'string' || id.trim() === '' || seen.has(id)) {
        throw new RangeError('Door and panel instance IDs must be nonempty and unique.');
    }
    seen.add(id);
}

function sameIdentities(configuration, doorIds, panelIdsByDoor) {
    return configuration.doorIds?.length === doorIds.length
        && doorIds.every((id, index) => id === configuration.doorIds[index])
        && configuration.panelIdsByDoor?.length === panelIdsByDoor.length
        && panelIdsByDoor.every((ids, index) => Array.isArray(configuration.panelIdsByDoor[index])
            && ids.length === configuration.panelIdsByDoor[index].length
            && ids.every((id, panelIndex) => id === configuration.panelIdsByDoor[index][panelIndex]));
}
