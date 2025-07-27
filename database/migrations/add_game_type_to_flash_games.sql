-- Add game_type column to flash_games table
-- Type A: existing games (managed in current game management)  
-- Type B: lesson-specific games (managed in lesson game settings)

ALTER TABLE public.flash_games 
ADD COLUMN game_type character(1) NOT NULL DEFAULT 'A' 
CHECK (game_type IN ('A', 'B'));

-- Add comment to explain the game types
COMMENT ON COLUMN public.flash_games.game_type IS 'Game type: A = General games (managed in game management), B = Lesson-specific games (managed in lesson game settings)';

-- Add group_game_type column to game_groups table
-- Type A: general game groups (managed in current game management)
-- Type B: lesson-specific game groups (managed in lesson game settings)

ALTER TABLE public.game_groups 
ADD COLUMN group_game_type character(1) NOT NULL DEFAULT 'A' 
CHECK (group_game_type IN ('A', 'B'));

-- Add comment to explain the group game types
COMMENT ON COLUMN public.game_groups.group_game_type IS 'Group game type: A = General game groups (managed in game management), B = Lesson-specific game groups (managed in lesson game settings)';