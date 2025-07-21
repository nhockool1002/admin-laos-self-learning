-- Database Schema for Badge Management System
-- Compatible with Supabase/PostgreSQL

-- Badges table
CREATE TABLE IF NOT EXISTS badges (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- User badges junction table
CREATE TABLE IF NOT EXISTS user_badges (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    badge_id BIGINT NOT NULL REFERENCES badges(id) ON DELETE CASCADE,
    awarded_at TIMESTAMPTZ DEFAULT NOW(),
    
    -- Ensure a user can only have one instance of each badge
    UNIQUE(user_id, badge_id)
);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_user_badges_user_id ON user_badges(user_id);
CREATE INDEX IF NOT EXISTS idx_user_badges_badge_id ON user_badges(badge_id);
CREATE INDEX IF NOT EXISTS idx_badges_created_at ON badges(created_at);

-- Add updated_at trigger for badges table
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_badges_updated_at BEFORE UPDATE ON badges
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Sample data (optional)
INSERT INTO badges (name, description, image_url) VALUES
('First Steps', 'Awarded for completing your first lesson', '/assets/images/badge_first_steps.png'),
('Quick Learner', 'Completed 5 lessons in one day', '/assets/images/badge_quick_learner.png'),
('Consistent Learner', 'Studied for 7 consecutive days', '/assets/images/badge_consistent.png'),
('Game Master', 'Achieved high score in all games', '/assets/images/badge_game_master.png'),
('Perfect Score', 'Scored 100% on a quiz', '/assets/images/badge_perfect.png')
ON CONFLICT (name) DO NOTHING;

-- Row Level Security (RLS) policies for Supabase
ALTER TABLE badges ENABLE ROW LEVEL SECURITY;
ALTER TABLE user_badges ENABLE ROW LEVEL SECURITY;

-- Allow public read access to badges
CREATE POLICY "Allow public read access to badges" ON badges
    FOR SELECT USING (true);

-- Allow authenticated users to read their own badges
CREATE POLICY "Users can view their own badges" ON user_badges
    FOR SELECT USING (auth.uid()::text = user_id::text);

-- Allow admins to manage badges (you'll need to modify this based on your user schema)
-- CREATE POLICY "Admins can manage badges" ON badges
--     FOR ALL USING (
--         EXISTS (
--             SELECT 1 FROM users 
--             WHERE id = auth.uid() AND is_admin = true
--         )
--     );

-- CREATE POLICY "Admins can manage user badges" ON user_badges
--     FOR ALL USING (
--         EXISTS (
--             SELECT 1 FROM users 
--             WHERE id = auth.uid() AND is_admin = true
--         )
--     );

-- Comments for documentation
COMMENT ON TABLE badges IS 'Stores information about available badges';
COMMENT ON COLUMN badges.name IS 'Unique name of the badge';
COMMENT ON COLUMN badges.description IS 'Description of what the badge represents';
COMMENT ON COLUMN badges.image_url IS 'Path to the badge image file';

COMMENT ON TABLE user_badges IS 'Junction table linking users to their earned badges';
COMMENT ON COLUMN user_badges.user_id IS 'Foreign key to users table';
COMMENT ON COLUMN user_badges.badge_id IS 'Foreign key to badges table';
COMMENT ON COLUMN user_badges.awarded_at IS 'Timestamp when the badge was awarded';