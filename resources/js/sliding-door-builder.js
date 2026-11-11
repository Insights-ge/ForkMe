export default function slidingDoorBuilder({ initialStep, payload }) {
    return {
        activeStep: initialStep,
        catalog: payload.catalog,
        metadata: payload.metadata,

        frameVisual(color) {
            return this.metadata.frameColors.find((frame) => frame.color === color)?.visual
                ?? this.metadata.neutralVisual;
        },

        infillVisual(infillId) {
            return this.metadata.infillVisuals.find((infill) => infill.infillId === infillId)?.visual
                ?? this.metadata.neutralVisual;
        },
    };
}
