# 📝 Router Documentation

This document outlines the routes of the application. It can be used to easily find the purpose of a route and the request and response data.

## Web Routes (`routes/web.php`)

- **GET /**
	- **Purpose:** Renders the single-page app entry (Inertia `Welcome` component).

- **POST /get-basic-info**
	- **Purpose:** Return basic Steam user information (player summaries) for a provided SteamID.
	- **Request (JSON body):**
		- `steamID` (required, numeric)
	- **Validation:** Controller validates `steamID` with `required|string`.
	- **Behavior:** Controller delegates to `App\Services\SteamAPIService::fetchPlayerSummary()` to call the Steam Web API using the server-side API key, logs and returns the Steam API JSON response.
	- **Response:** Steam player summary JSON or error JSON on failure.
