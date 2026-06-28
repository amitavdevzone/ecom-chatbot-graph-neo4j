## 1. Controller Update

- [ ] 1.1 Remove the hardcoded `$query = 'mobile';` override from `ProductSearchController::__invoke()`
- [ ] 1.2 Add validation rules for optional `filter_by` (nullable string) and `sort_by` (nullable string) fields
- [ ] 1.3 Read `filter_by` and `sort_by` from the request and build a conditional `$options` array
- [ ] 1.4 Chain `->options($options)` on the Scout search builder when options are present

## 2. Tests

- [ ] 2.1 Update the existing feature test to assert the hardcoded `"mobile"` override is gone (query is passed through as-is)
- [ ] 2.2 Add a test that sends `filter_by` and verifies it is forwarded to Typesense (mock Scout or use a fake)
- [ ] 2.3 Add a test that sends `sort_by` and verifies it is forwarded
- [ ] 2.4 Add a test that sends both `filter_by` and `sort_by` together
- [ ] 2.5 Add a test that omits both fields and verifies the search still works with only `query`

## 3. Spec Sync

- [ ] 3.1 Run `openspec sync-specs --change product-search-filter-sort` to merge the delta spec into the main `openspec/specs/product-search-api/spec.md`
