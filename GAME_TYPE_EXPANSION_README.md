# Game Type Expansion Implementation

## Overview

This implementation extends the existing game functionality by adding a `game_type` field to categorize games into two types:

- **Type A**: General games (managed through existing "Quản lý trò chơi" section)
- **Type B**: Lesson-specific games (managed through new "Cài đặt trò chơi theo bài học" section)

## Database Changes

### 1. Schema Migration

Apply this SQL to add the `game_type` column:

```sql
-- Add game_type column to flash_games table
ALTER TABLE public.flash_games 
ADD COLUMN game_type character(1) NOT NULL DEFAULT 'A' 
CHECK (game_type IN ('A', 'B'));

COMMENT ON COLUMN public.flash_games.game_type IS 'Game type: A = General games (managed in game management), B = Lesson-specific games (managed in lesson game settings)';

-- Add group_game_type column to game_groups table
ALTER TABLE public.game_groups 
ADD COLUMN group_game_type character(1) NOT NULL DEFAULT 'A' 
CHECK (group_game_type IN ('A', 'B'));

COMMENT ON COLUMN public.game_groups.group_game_type IS 'Group game type: A = General game groups (managed in game management), B = Lesson-specific game groups (managed in lesson game settings)';
```

### 2. Data Migration

All existing games and game groups will automatically be set to type 'A' (general games/groups) due to the DEFAULT value.

## Backend Implementation

### 1. Service Layer Updates

**SupabaseService.php** - Enhanced with:

- Modified existing methods to filter for type 'A' games and game groups only
- Added new methods for lesson games (type 'B'):
  - `getLessonGames()`, `createLessonGame()`, `updateLessonGame()`, `deleteLessonGame()`
  - `getLessonGameGroups()`, `createLessonGameGroup()`, `updateLessonGameGroup()`, `deleteLessonGameGroup()`
- All game group operations now include `group_game_type` filtering
- Complete separation between Type A and Type B game groups

### 2. Controller Implementation

**New LessonGameController.php** - Handles lesson game management:

- CRUD operations for lesson games (type B)
- CRUD operations for lesson game groups
- Same interface as GameController but for different game type

### 3. Route Configuration

**routes/web.php** - Added new routes:

```php
// Lesson game management routes
Route::get('/supabase/lesson-games', [LessonGameController::class, 'index']);
Route::post('/supabase/lesson-games', [LessonGameController::class, 'store']);
Route::get('/supabase/lesson-games/{id}', [LessonGameController::class, 'show']);
Route::put('/supabase/lesson-games/{id}', [LessonGameController::class, 'update']);
Route::delete('/supabase/lesson-games/{id}', [LessonGameController::class, 'destroy']);

Route::get('/supabase/lesson-game-groups', [LessonGameController::class, 'listGroups']);
Route::post('/supabase/lesson-game-groups', [LessonGameController::class, 'createGroup']);
Route::get('/supabase/lesson-game-groups/{id}', [LessonGameController::class, 'showGroup']);
Route::put('/supabase/lesson-game-groups/{id}', [LessonGameController::class, 'updateGroup']);
Route::delete('/supabase/lesson-game-groups/{id}', [LessonGameController::class, 'deleteGroup']);
```

## Frontend Implementation

### 1. Navigation Updates

**Sidebar** - Updated to include new menu item:

- Added "Cài đặt trò chơi theo bài học" under "Quản lý Khoá học" section
- Updated active state detection for proper menu highlighting

### 2. New Page Implementation

**lesson-games.blade.php** - New page with:

- Tab-based interface (Groups and Games tabs)
- Complete CRUD functionality for both lesson game groups and lesson games
- Same UI patterns as existing game management pages
- Automatic game_type handling (hidden field set to 'B')

## Key Features

### 1. Separation of Concerns

- **Existing Game Management**: Only shows/manages Type A games and Type A game groups
- **Lesson Game Settings**: Only shows/manages Type B games and Type B game groups
- Both use the same underlying database tables but with complete type filtering
- Game groups are now also separated by type, preventing cross-contamination

### 2. Hidden Field Implementation

- Game type and group game type are automatically set based on the management interface used
- Users cannot manually change game types or group game types through the UI
- Type A games and groups can only be modified through existing game management
- Type B games and groups can only be modified through lesson game settings
- Complete isolation ensures data integrity between the two systems

### 3. Backward Compatibility

- All existing games and game groups remain as Type A by default
- Existing game management functionality is unchanged
- No impact on current game data or workflows
- Complete backward compatibility for both games and game groups

## API Endpoints

### Existing Game Management (Type A)
- `/supabase/games` - Now filtered to show only Type A games
- `/supabase/game-groups` - Now filtered to show only Type A game groups

### Lesson Game Management (Type B)
- `/supabase/lesson-games` - CRUD for Type B games
- `/supabase/lesson-game-groups` - CRUD for Type B game groups

## Usage Instructions

### 1. Apply Database Migration

Execute the SQL migration to add the `game_type` column:

```bash
# Apply migration to your database
psql -d your_database -f database/migrations/add_game_type_to_flash_games.sql
```

### 2. Access New Functionality

1. Navigate to admin panel
2. Go to "Quản lý Khoá học" → "Cài đặt trò chơi theo bài học"
3. Use the tab interface to manage:
   - **Nhóm trò chơi**: Create/edit Type B game groups for lessons
   - **Trò chơi**: Create/edit Type B lesson-specific games

### 3. Game Type Behavior

- **Type A games and groups**: Managed through existing "Quản lý trò chơi" section
- **Type B games and groups**: Managed through new "Cài đặt trò chơi theo bài học" section
- Games and groups cannot be moved between types through the UI
- Complete separation: Type A games can only use Type A groups, Type B games can only use Type B groups

## Testing

1. Verify existing game management still works (shows only Type A games and Type A groups)
2. Create new lesson games and groups through the new interface (should be Type B)
3. Verify separation: Type A and Type B games/groups should not appear in each other's management interfaces
4. Verify that Type B games can only select Type B groups in dropdowns
5. Test all CRUD operations for both types (games and groups)

## File Changes Summary

### New Files
- `app/Http/Controllers/LessonGameController.php`
- `resources/views/lesson-games.blade.php`
- `database/migrations/add_game_type_to_flash_games.sql`
- `GAME_TYPE_EXPANSION_README.md`

### Modified Files
- `app/Services/SupabaseService.php` - Added lesson game methods and type filtering
- `routes/web.php` - Added lesson game routes and view route
- `resources/views/components/sidebar.blade.php` - Added new menu item

## Future Enhancements

1. **Game Type Migration Tool**: Admin interface to move games between types
2. **Lesson Association**: Link lesson games directly to specific lessons
3. **Game Analytics**: Separate analytics for different game types
4. **Bulk Operations**: Batch operations for lesson games