<?php

namespace App\Data;

use JsonSerializable;

readonly class BuilderBrowserPayloadData implements JsonSerializable
{
    public function __construct(
        public BuilderCatalogData $catalog,
        public BuilderMetadataData $metadata,
    ) {}

    /**
     * Metadata contains presentation presets and visual mappings.
     * It must not be merged into catalog records or imply manufacturing rules.
     *
     * @return array{catalog: BuilderCatalogData, metadata: BuilderMetadataData}
     */
    public function jsonSerialize(): array
    {
        return [
            'catalog' => $this->catalog,
            'metadata' => $this->metadata,
        ];
    }
}
