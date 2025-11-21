# 📝 Router Documentation

This document outlines the routes of the application. It can be used to easily find the purpose of a route and the request and response data.

## Web Routes (`routes/web.php`)

 -   **GET /**
 	- **Purpose:** Render the landing page where users can submit their Steam profile (uses Inertia).
 	- **Parameters:** None (standard GET)
 	- **Response:** HTML page rendered via Inertia. The frontend component is `resources/js/Pages/Landing.jsx` and handles the Steam ID submission flow.
 	- **Notes:** This is an entry point for unauthenticated users; it does not return JSON.

- **POST /validate-user**
	- **Purpose:** Validate whether a Steam user exists for a provided Steam profile input.
	- **Request (JSON body):**
		- `userSteamID` (required, string) — Steam profile URL, numeric SteamID, or vanity name
		- `isCustomID` (required, boolean) — whether the provided `userSteamID` is a custom vanity name that requires resolution
	- **Validation:** Controller validates `userSteamID` (`required|string`) and `isCustomID` (`required|boolean`).
	- **Behavior:** Controller delegates to `App\Services\SteamAPIService::fetchPlayerSummary()` (service resolves vanity names when needed). The controller returns a consistent JSON shape so the frontend always receives the same structure whether the user exists or not.
	- **Response (JSON body):**
		- `200 OK` — user found
			- Body: `{ "userSteamID": "76561199...", "isAvailable": true }`
		- `200 OK` — user not found
			- Body: `{ "userSteamID": null, "isAvailable": false }`
		- `502 / 500` — upstream error or internal error (body retains same shape)
			- Body: `{ "userSteamID": null, "isAvailable": false }`

	Notes:
	- `userSteamID` is returned as a string (not a numeric JSON value) to avoid integer precision loss in JavaScript. Treat it as a string in the frontend.
	- If `isCustomID` is `true`, the service will attempt to resolve the vanity name; if resolution fails the controller treats the result as "not found" and returns `{ userSteamID: null, isAvailable: false }`.
