# 📝 Router Documentation

This document outlines the routes of the application. It can be used to easily find the purpose of a route and the request and response data.

## Web Routes (`routes/web.php`)

 -   **GET /**
 	- **Purpose:** Render the landing page where users can submit their Steam profile (uses Inertia).
 	- **Parameters:** None (standard GET)
 	- **Response:** HTML page rendered via Inertia. The frontend component is `resources/js/Pages/Landing.jsx` and handles the Steam ID submission flow.
 	- **Notes:** This is an entry point for unauthenticated users; it does not return JSON.

---
- **GET /dashboard**
	- **Purpose:** Render the dashboard for users who have been validated via Steam. This route requires a server-side session key `userSteamID` and is protected by the `steam.auth` middleware.
	- **Parameters:** None (standard GET)
	- **Response:** HTML page rendered via Inertia. The frontend component is `resources/js/Pages/Dashboard.jsx` (or `Dashboard.jsx`) and expects the server session to contain `userSteamID`.

---

- **POST /api/classic**
	- **Purpose:** Get a random game from the user's library with hints for the Classic gamemode. This route is protected by the `steam.auth` middleware.
	- **Parameters:** None (standard POST)
	- **Response (JSON body):** Returns hints organized by difficulty level (easy, medium, hard), each containing the hint name, required data keys, and fetched data.
	- **Example Response:**
	```json
	{
		"easy": {
			"hint_name": "first_letter",
			"needed_data_keys": ["first_letter"],
			"data": {
				"first_letter": "H"
			}
		},
		"medium": {
			"hint_name": "release_date",
			"needed_data_keys": ["release_date"],
			"data": {
				"release_date": "Nov 16, 2011"
			}
		},
		"hard": {
			"hint_name": "reviews",
			"needed_data_keys": ["review_ratio", "total_reviews"],
			"data": {
				"review_ratio": "94.2%",
				"total_reviews": 512847
			}
		}
	}
	```
	- **Error Response:** If no games are found in the session:
	```json
	{
		"error": "No games found in session"
	}
	```
	- **Notes:** 
		- One random hint is selected per difficulty level
		- The game being guessed is selected randomly from `session('allGames')`
		- Hint data is fetched from Steam Store API and SteamSpy API as needed

---

- **POST /initiate-user**
	- **Purpose:** Validate whether a Steam user exists for a provided Steam profile input and store minimal user/session data.
	- **Request (JSON body):**
		- `userSteamID` (required, string) — Steam profile URL, numeric SteamID, or vanity name
		- `isCustomID` (required, boolean) — whether the provided `userSteamID` is a custom vanity name that requires resolution
	- **Validation:** Controller validates `userSteamID` (`required|string`) and `isCustomID` (`required|boolean`).
	- **Behavior:** Controller delegates to the backend services which resolve vanity names when needed and look up the Steam player summary. The controller returns a numeric validation code (see below) as the JSON response body. On success (public profile) it stores a trimmed set of user/stats keys in the session which are exposed to the frontend via the shared Inertia `steam` prop.
	- **Response (JSON body):**
		- `1` — invalid user (not found or vanity resolution failed)
		- `2` — valid Steam user but private profile
		- `3` — valid Steam user with public profile

	Notes:
	- The controller returns a numeric JSON body (e.g. `3`) representing the validation outcome. This differs from an earlier shape that returned an object — treat the response as an integer code.
	- `userSteamID` values are stored and communicated as strings (not raw numbers) to avoid integer precision loss in JavaScript.
	- When a public profile is found the server stores session keys and exposes them via Inertia as `page.props.steam`. The keys available in `steam` are:
		- `steamID` (string|null) — the numeric SteamID as a string
		- `personaState` (string) — a human readable persona state. Keys + values:
    		- 0 => Offline
    		- 1 => Online
    		- 2 => Busy
    		- 3 => Away
    		- 4 => Snooze
    		- 5 => Looking to trade
    		- 6 => Looking to play
		- `publicProfile` (bool)
		- `steamProfileURL` (string|null) — the full Steam profile URL
		- `profilePictureURL` (string|null) — URL to the Steam profile avatar
		- `username` (string|null)
	- `timeCreated` (string|null) — formatted creation date (e.g., "January 1, 2001")
	- `accountAge` (object|null) — breakdown of years/months/days (if available)
	- `totalGamesOwned` (int)
	- `allGames` (array of objects)
		- Each object has the following keys:
			- `id` (int) — the Steam App ID of the game
			- `name` (string) — the name of the game
			- `cover_url` (string|null) — URL to the game's cover image (may be null if unavailable)
			- `playtime` (int) — total playtime in minutes
			- `last_played` (int|null) — Unix timestamp of when the game was last played (null if never played)
	- `totalPlaytimeMinutes` (int)
	- `averagePlaytimeMinutes` (int)
	- `topGames` (array)
	- `playedPercentage` (float 0..100)
	- Frontend components can access these values through `usePage().props.steam` (Inertia).

- **GET /admin**
  - **Purpose:** This endpoint is an easteregg. The following endpoints also redirect to /admin:
    - /login
    - /profile
    - /settings
    - /account
