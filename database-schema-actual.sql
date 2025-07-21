-- Database Schema for Badge Management System
-- Based on actual Supabase implementation shown

-- Badges table (actual name: badges_system)
CREATE TABLE IF NOT EXISTS badges_system (
    id TEXT PRIMARY KEY,
    name TEXT NOT NULL,
    description TEXT NOT NULL,
    image_path TEXT NOT NULL,
    condition TEXT
);

-- User badges junction table
CREATE TABLE IF NOT EXISTS user_badges (
    id SERIAL PRIMARY KEY,
    username TEXT NOT NULL,
    badge_id TEXT NOT NULL REFERENCES badges_system(id) ON DELETE CASCADE,
    achieved_date TIMESTAMP DEFAULT NOW(),
    
    -- Ensure a user can only have one instance of each badge
    UNIQUE(username, badge_id)
);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_user_badges_username ON user_badges(username);
CREATE INDEX IF NOT EXISTS idx_user_badges_badge_id ON user_badges(badge_id);

-- Sample data matching your structure
INSERT INTO badges_system (id, name, description, image_path, condition) VALUES
('badge_001', 'Huy hiệu Phụ Âm I', 'Hoàn thành bài luyện tập phụ âm với điểm cao', '/badges/best-practice.png', 'nan'),
('badge_002', 'Huy hiệu Phiên Âm I', 'Hoàn thành bài luyện tập phiên âm với điểm cao', '/badges/nguyenam1.png', 'nan')
ON CONFLICT (id) DO NOTHING;

-- Sample user badge data
INSERT INTO user_badges (username, badge_id, achieved_date) VALUES
('nhockool002', 'badge_001', '2025-06-12 16:06:14.848')
ON CONFLICT (username, badge_id) DO NOTHING;

-- Row Level Security (RLS) policies for Supabase
ALTER TABLE badges_system ENABLE ROW LEVEL SECURITY;
ALTER TABLE user_badges ENABLE ROW LEVEL SECURITY;

-- Allow public read access to badges
CREATE POLICY "Allow public read access to badges_system" ON badges_system
    FOR SELECT USING (true);

-- Allow authenticated users to read badges (adjust based on your auth system)
CREATE POLICY "Users can view user_badges" ON user_badges
    FOR SELECT USING (true);

-- Comments for documentation
COMMENT ON TABLE badges_system IS 'Stores information about available badges';
COMMENT ON COLUMN badges_system.id IS 'Unique text identifier for the badge';
COMMENT ON COLUMN badges_system.name IS 'Display name of the badge';
COMMENT ON COLUMN badges_system.description IS 'Description of what the badge represents';
COMMENT ON COLUMN badges_system.image_path IS 'Path to the badge image file';
COMMENT ON COLUMN badges_system.condition IS 'Condition for earning the badge';

COMMENT ON TABLE user_badges IS 'Junction table linking users to their earned badges';
COMMENT ON COLUMN user_badges.username IS 'Username of the user who earned the badge';
COMMENT ON COLUMN user_badges.badge_id IS 'Foreign key to badges_system table';
COMMENT ON COLUMN user_badges.achieved_date IS 'Timestamp when the badge was awarded';