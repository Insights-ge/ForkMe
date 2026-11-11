# Sliding door builder browser payload

The authenticated Filament page embeds this payload using Blade's `@js()` escaping. It is available only after the page's `View:SlidingDoorBuilder` permission check. There is no separate catalog endpoint. Alpine receives the payload when the page mounts; it is not a public Livewire property and is not saved as a user configuration.

`BuilderBrowserPayloadData` defines the payload structure. Field names use camelCase, IDs are JSON integers, collections are JSON arrays, and enum values are stable strings rather than translated labels. The machine-readable contract is in [sliding-door-builder-payload.schema.json](../resources/schemas/sliding-door-builder-payload.schema.json).

```json
{
  "catalog": {
    "models": [],
    "infills": []
  },
  "metadata": {}
}
```

## Catalog facts

`catalog` comes from `BuilderCatalogReadService`. It contains active models and their actual variants, component references and recorded infill exclusions. Infill records are listed independently; compatibility depends on the components used by a selection.

| Record | Fields |
| --- | --- |
| Model | `id`, `name`, `minHeight`, `maxHeight`, `minWidth`, `maxWidth`, nullable `notRecommendedHeightMin` and `notRecommendedHeightMax`, `variants` |
| Variant | `id`, `name` (product full name), `color`, `frameDesignCode`, `heightRangeMin`, `heightRangeMax`, `hasPartition`, `hasSoftCloseMechanism`, `components` |
| Component compatibility | `id`, `type`, `incompatibleInfillIds` |
| Infill | `id`, `code`, `description` |

Dimensions retain the catalog's millimetre values. The payload does not determine whether a dimension applies to an opening or individual door, or calculate track deductions, overlaps or cutting sizes. A missing discouraged-height limit remains `null`.

`hasPartition` and `hasSoftCloseMechanism` indicate component presence, not installation eligibility or a user's selection. Component types are `side_profile`, `top_rail`, `bottom_rail`, `partition`, and `soft_close_mechanism`. Each `incompatibleInfillIds` array contains exclusions for that component. An empty array means no recorded exclusions; it is not evidence of manufacturing approval.

Colours are `black`, `inox`, `graphite`, and `silver`. Names and descriptions are catalog text; colour labels and other interface strings continue to use the existing English/Georgian translations. Supplier unique codes, timestamps and admin status fields are excluded.

## Metadata

`metadata` is currently an empty **object**. It reserves a separate namespace for future layout presets, frame colour renderings, infill visual mappings and explicitly provisional assumptions. Those features must extend their DTOs and this contract when implemented. They must not add fabricated catalog records, override compatibility facts, or introduce default user selections.

Metadata is an internal data category, not a label shown in the interface. Empty catalog data remains empty; neither PHP nor JavaScript supplies fallback products.

## Contract changes

Update the DTO, JSON Schema, browser consumer, tests and this document together whenever the payload structure changes. Browser copies are presentation data; future saving or ordering must revalidate IDs and rules against the server catalog.
