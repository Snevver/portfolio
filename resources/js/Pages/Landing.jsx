import React, { useState } from "react";
import Card from "../Components/Card";

export default function Landing() {
    const [steamID, setSteamID] = useState("");
    const [userData, setUserData] = useState(null);

    /**
     * Fetches the user data from the Steam API using the submitSteamID function.
     * @param {*} event - The event object, used to prevent the default behavior.
     */
    async function getBasicData(event) {
        event.preventDefault();

        const csrfToken = document.querySelector(
            'meta[name="csrf-token"]'
        )?.content;

        await submitSteamID(steamID, csrfToken);
    }

    /**
     *
     * @param {*} steamID
     * @param {*} csrfToken
     */
    async function submitSteamID(steamID, csrfToken) {
        try {
            // Send POST request to the API route
            const response = await fetch("/get-basic-info", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({ steamID }),
            });

            let api;
            if (response.ok) {
                try {
                    api = await response.json();
                } catch (jsonError) {
                    throw new Error("Failed to parse JSON response.");
                }
            } else {
                // Try to parse error message from JSON, fallback to text
                let errorMessage = `Request failed with status ${response.status}`;
                try {
                    const errorData = await response.json();
                    if (errorData && errorData.error) {
                        errorMessage = errorData.error;
                    }
                } catch {
                    // Not JSON, try text
                    const errorText = await response.text();
                    if (errorText) {
                        errorMessage = errorText;
                    }
                }
                throw new Error(errorMessage);
            }

            if (api.error) {
                throw new Error(api.error);
            }

            // Extract the first player object from the Steam API response
            const player = api.response?.players?.[0];

            if (!player) {
                throw new Error("No Steam user found with that ID or URL.");
            }

            // Put user data into new object and set state
            setUserData({ ...player });
        } catch (error) {
            console.error("Error fetching Steam data:", error);
        }
    }

    return (
        <div style={containerStyle}>
            {/* Show either form or username + profile picture depending on whether userData is available */}
            {userData ? (
                <div style={{ color: "#fff" }}>
                    Username: {userData.personaname} <br />
                    <img src={userData.avatarfull} />
                </div>
            ) : (
                <div>
                    <form onSubmit={getBasicData}>
                        <input
                            value={steamID}
                            required
                            placeholder="Enter Steam profile URL, Steam ID, or custom ID"
                            onChange={(event) => setSteamID(event.target.value)}
                        />
                        <button type="submit" style={{ color: "#fff" }}>
                            Submit
                        </button>
                    </form>
                </div>
            )}

            <Card></Card>
        </div>
    );
}
