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
		- `publicProfile` (bool)
		- `steamProfileURL` (string|null) — the full Steam profile URL
		- `profilePictureURL` (string|null) — URL to the Steam profile avatar
		- `username` (string|null)
		- `timeCreated` (object|null) — normalized creation data (see backend docs)
		- `accountAge` (object|null) — breakdown of years/months/days (if available)
		- `totalGamesOwned` (int)
		- `gameIDsOwned` (array)
		- `totalPlaytimeMinutes` (int)
		- `averagePlaytimeMinutes` (float)
		- `medianPlaytimeMinutes` (float)
		- `topGames` (array)
		- `playedPercentage` (float 0..1)
	- Frontend components can access these values through `usePage().props.steam` (Inertia).
