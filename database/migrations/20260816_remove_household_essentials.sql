-- Remove Household Essentials from the public food catalogue while retaining
-- its rows for historical orders and back-office reporting.

UPDATE products
SET is_active = 0, is_featured = 0
WHERE category_id = (SELECT id FROM categories WHERE slug = 'household-essentials');

UPDATE categories
SET is_active = 0
WHERE slug = 'household-essentials';
