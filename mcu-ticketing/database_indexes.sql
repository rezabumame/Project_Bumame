-- Performance Optimization Indexes for Bumame Ticketing System
-- Run this SQL to add indexes for better query performance
-- Estimated improvement: 10-100x faster queries on large datasets

-- ============================================
-- PROJECTS TABLE INDEXES
-- ============================================

-- Index for status filtering (frequently used in dashboards)
CREATE INDEX IF NOT EXISTS idx_projects_status ON projects(status_project);

-- Index for sales person queries
CREATE INDEX IF NOT EXISTS idx_projects_sales_person ON projects(sales_person_id);

-- Index for korlap queries
CREATE INDEX IF NOT EXISTS idx_projects_korlap ON projects(korlap_id);

-- Index for date-based queries and sorting
CREATE INDEX IF NOT EXISTS idx_projects_created ON projects(created_at);

-- Index for MCU date filtering
CREATE INDEX IF NOT EXISTS idx_projects_tanggal_mcu ON projects(tanggal_mcu);

-- Composite index for common query patterns
CREATE INDEX IF NOT EXISTS idx_projects_status_created ON projects(status_project, created_at);


-- ============================================
-- RABS TABLE INDEXES
-- ============================================

-- Index for RAB status filtering
CREATE INDEX IF NOT EXISTS idx_rabs_status ON rabs(status);

-- Index for project relationship
CREATE INDEX IF NOT EXISTS idx_rabs_project ON rabs(project_id);

-- Index for created date sorting
CREATE INDEX IF NOT EXISTS idx_rabs_created ON rabs(created_date);

-- Index for creator queries
CREATE INDEX IF NOT EXISTS idx_rabs_creator ON rabs(created_by);

-- Composite index for approval workflow queries
CREATE INDEX IF NOT EXISTS idx_rabs_status_created ON rabs(status, created_date);


-- ============================================
-- INVENTORY REQUESTS TABLE INDEXES
-- ============================================

-- Index for project relationship
CREATE INDEX IF NOT EXISTS idx_inventory_project ON inventory_requests(project_id);

-- Index for status filtering
CREATE INDEX IF NOT EXISTS idx_inventory_status ON inventory_requests(status);

-- Index for creator queries
CREATE INDEX IF NOT EXISTS idx_inventory_creator ON inventory_requests(created_by);

-- Index for date sorting
CREATE INDEX IF NOT EXISTS idx_inventory_created ON inventory_requests(created_at);


-- ============================================
-- WAREHOUSE REQUESTS TABLE INDEXES
-- ============================================

-- Index for inventory request relationship
CREATE INDEX IF NOT EXISTS idx_warehouse_inventory ON warehouse_requests(inventory_request_id);

-- Index for warehouse type filtering
CREATE INDEX IF NOT EXISTS idx_warehouse_type ON warehouse_requests(warehouse_type);

-- Index for status filtering
CREATE INDEX IF NOT EXISTS idx_warehouse_status ON warehouse_requests(status);


-- ============================================
-- USERS TABLE INDEXES
-- ============================================

-- Index for role-based queries
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);

-- Index for username login
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);


-- ============================================
-- PROJECT LOGS TABLE INDEXES
-- ============================================

-- Index for project relationship
CREATE INDEX IF NOT EXISTS idx_project_logs_project ON project_logs(project_id);

-- Index for user activity tracking
CREATE INDEX IF NOT EXISTS idx_project_logs_user ON project_logs(user_id);

-- Index for timestamp sorting
CREATE INDEX IF NOT EXISTS idx_project_logs_timestamp ON project_logs(timestamp);

-- Composite index for project activity queries
CREATE INDEX IF NOT EXISTS idx_project_logs_project_timestamp ON project_logs(project_id, timestamp);


-- ============================================
-- MAN POWERS & PROJECT MAN POWER INDEXES (NEW)
-- ============================================

-- Index for man power search
CREATE INDEX IF NOT EXISTS idx_man_powers_name ON man_powers(name);
CREATE INDEX IF NOT EXISTS idx_man_powers_status ON man_powers(status);
CREATE INDEX IF NOT EXISTS idx_man_powers_is_active ON man_powers(is_active);

-- Index for project assignments by date (Heatmap & Calendar)
CREATE INDEX IF NOT EXISTS idx_pmp_date ON project_man_power(date);
CREATE INDEX IF NOT EXISTS idx_pmp_project_date ON project_man_power(project_id, date);

-- Index for overlap checking (Critical for assignment validation)
CREATE INDEX IF NOT EXISTS idx_pmp_man_power_date ON project_man_power(man_power_id, date);


-- ============================================
-- VERIFICATION
-- ============================================

-- Run this to verify indexes were created
SHOW INDEX FROM projects;
SHOW INDEX FROM rabs;
SHOW INDEX FROM inventory_requests;
SHOW INDEX FROM warehouse_requests;
SHOW INDEX FROM users;
SHOW INDEX FROM project_logs;
SHOW INDEX FROM man_powers;
SHOW INDEX FROM project_man_power;

-- ============================================
-- PERFORMANCE TESTING
-- ============================================

-- Test query performance before/after indexes
-- EXPLAIN SELECT * FROM projects WHERE status_project = 'active' ORDER BY created_at DESC LIMIT 50;
-- EXPLAIN SELECT * FROM rabs WHERE status = 'need_approval_manager' ORDER BY created_date DESC;
