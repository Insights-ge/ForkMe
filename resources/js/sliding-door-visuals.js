export function frameVisual(metadata, color) {
    return metadata.frameColors.find((frame) => frame.color === color)?.visual ?? metadata.neutralVisual;
}

export function infillVisual(metadata, infillId) {
    return metadata.infillVisuals.find((infill) => infill.infillId === infillId)?.visual ?? metadata.neutralVisual;
}
