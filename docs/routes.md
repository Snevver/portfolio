# 📝 Router Documentation

This document outlines the routes of the application. It can be used to easily find the purpose of a route and the request and response data.

## Web Routes (`routes/web.php`)

-   **GET /**

- **POST /validate-user**
	- **Purpose:** Validate whether a Steam user exists for a provided Steam profile input.
	- **Request (JSON body):**
		- `steamID` (required, string) — Steam profile URL, numeric SteamID, or vanity name
		- `isCustomID` (required, boolean) — whether the provided `steamID` is a custom vanity name that requires resolution
	- **Validation:** Controller validates `steamID` (`required|string`) and `isCustomID` (`required|boolean`).
	- **Behavior:** Controller delegates to `App\Services\SteamAPIService::fetchPlayerSummary()` (service resolves vanity names when needed), then returns an HTTP status indicating existence.
	- **Response:**
		- `204 No Content` — Steam user exists (empty body)
		- `404 Not Found` — Steam user does not exist or resolution failed
