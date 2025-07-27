-- Add game_type column to flash_games table
-- Type A: existing games (managed in current game management)  
-- Type B: lesson-specific games (managed in lesson game settings)

ALTER TABLE public.flash_games 
ADD COLUMN game_type character(1) NOT NULL DEFAULT 'A' 
CHECK (game_type IN ('A', 'B'));

-- Add comment to explain the game types
COMMENT ON COLUMN public.flash_games.game_type IS 'Game type: A = General games (managed in game management), B = Lesson-specific games (managed in lesson game settings)';