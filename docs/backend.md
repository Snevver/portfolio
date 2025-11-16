# Backend best practices

## 1. File naming conventions

### Controllers
- Use **PascalCase** for controller names
- Always suffix with `Controller`
- Use singular nouns for resource controllers
- Examples: `UserController`, `GameController`, `LeaderboardController`

### Models
- Use **PascalCase** for model names
- Use singular nouns
- Examples: `User`, `Game`, `Score`, `Achievement`

### Routes
- Use **kebab-case** for URL segments
- Use plural nouns for resource routes
- Examples: `/games`, `/user-profiles`, `/game-sessions`

## 2. MVC architecture

### Models
- Keep models focused on data representation and business logic
- Use Eloquent relationships to define model associations
- Implement validation rules within models when appropriate
- Use accessors and mutators for data formatting

```php
class Game extends Model
{
    protected $fillable = [
        'title', 
        'description', 
        'difficulty'
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'is_active' => 'boolean'
    ];
    
    public function users()
    {
        return $this->belongsToMany(User::class, 'game_sessions');
    }
}
```

### Controllers
- Keep controllers thin - business logic is done in services or models
- Follow RESTful conventions for resource controllers
- Use dependency injection for services and repositories
- Return appropriate HTTP status codes

```php
class GameController extends Controller
{
    public function __construct(
        private GameService $gameService
    ) {}
    
    public function index()
    {
        $games = $this->gameService->getActiveGames();
        // Pass back to the front end
    }
}
```

## 3. Laravel best practices

### Validation
- Use Form Request classes for complex validation rules
- Keep validation logic separate from controllers
- Create custom validation rules for business-specific logic

```php
class StoreGameRequest extends FormRequest
{
    public function rules()
    {
        return [
            'title' => 'required|string|max:255|unique:games',
            'difficulty' => 'required|in:easy,medium,hard',
            'description' => 'nullable|string|max:1000'
        ];
    }
}
```

### Service classes
- Create service classes for complex business logic
- Keep services focused on a single responsibility
- Use dependency injection for service dependencies

```php
class GameService
{
    public function __construct(
        private GameRepository $gameRepository,
        private ScoreCalculator $scoreCalculator
    ) {}
    
    public function createGame(array $data): Game
    {
        // Business logic here
        return $this->gameRepository->create($data);
    }
}
```

## 4. Code organization

### Directory structure
- Follow Laravel's conventional directory structure
- Create additional directories for custom classes (Services for example)
- Group related functionality together

```
app/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Models/
├── Services/
└── Providers/
```

### Namespacing
- Use proper PSR-4 autoloading standards
- Organize classes in logical namespaces
- Import classes at the top of files

```php
<?php

namespace App\Services;

use App\Models\Game;
use App\Repositories\GameRepository;
use Illuminate\Support\Collection;

class GameService
{
    // Class implementation
}
```

## 5. Error Handling

### Exception Handling
- Use Laravel's built-in exception handling
- Create custom exception classes for domain-specific errors
- Always log exceptions with appropriate context

```php
try {
    $game = $this->gameService->createGame($request->validated());
} catch (GameCreationException $e) {
    Log::error('Game creation failed', [
        'user_id' => auth()->id(),
        'data' => $request->validated(),
        'error' => $e->getMessage()
    ]);
    
    return back()->withErrors(['error' => 'Failed to create game']);
}
```

### Validation errors
- Return meaningful error messages
- Use Laravel's validation message system
- Provide user-friendly error feedback

## 6. Security best practices

### Data protection
- Use Laravel's CSRF protection for forms
- Validate and sanitize all user inputs
- Use mass assignment protection with `$fillable` or `$guarded`

### API security
- Validate API requests thoroughly

## 7. Testing

### Test structure
- Write unit tests for models and services
- Create feature tests for complete user workflows
- Use factories for test data generation

### Test naming
- Use descriptive test method names
- Follow the pattern: `test_it_can_do_something()`
- Group related tests in the same test class

```php
class GameTest extends TestCase
{
    public function test_it_can_create_a_game_with_valid_data()
    {
        $gameData = Game::factory()->make()->toArray();
        
        $response = $this->post('/games', $gameData);
        
        $response->assertStatus(201);
        $this->assertDatabaseHas('games', $gameData);
    }
}
```

## 8. Documentation

### Code documentation
- Write clear and concise docblocks for methods
- Document complex business logic
- Keep documentation up-to-date with code changes

### API documentation
- Document all API endpoints with proper examples
- Include request/response schemas
- Specify authentication requirements

## 9. Environment configuration

### Configuration management
- Use environment variables for configuration
- Never commit sensitive data to version control
- Use different configurations for different environments

---