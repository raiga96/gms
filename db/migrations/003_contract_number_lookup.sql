-- Add a lookup index without changing the legacy TEXT column or its accepted values.
-- The 100-character prefix stays below older InnoDB key-length limits; equality
-- comparisons still verify the full value after the index lookup.
ALTER TABLE contract ADD INDEX idx_contract_number (contract_number(100));
