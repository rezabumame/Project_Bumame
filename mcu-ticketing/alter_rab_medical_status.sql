-- Add 'completed' to rab_medical_results.status enum (run once on existing DB)
-- Required for: store_realization / markAsCompleted

ALTER TABLE rab_medical_results
MODIFY COLUMN status enum('draft','submitted','approved_manager','approved_head','rejected','completed') DEFAULT 'draft';
