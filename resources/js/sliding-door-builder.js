export default function slidingDoorBuilder({ initialStep, payload }) {
    return {
        activeStep: initialStep,
        catalog: payload.catalog,
        metadata: payload.metadata,
    };
}
