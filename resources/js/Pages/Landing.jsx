import React, { useState, useEffect } from "react";
import Card from "../Components/Card";

export default function Landing() {
    const [steamID, setSteamID] = useState("");
    const [userData, setUserData] = useState(null);
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState(null);
    const [isValidInput, setIsValidInput] = useState(null);
    const [isHelpModalOpen, setIsHelpModalOpen] = useState(false);

    /**
     * Validates if the input is a valid Steam ID or URL.
     * Accepts:
     * - Full Steam profile URL: https://steamcommunity.com/profiles/76561198000000000
     * - Full custom Steam URL: https://steamcommunity.com/id/customname
     * - Steam ID only: 76561198000000000 (17 digits, typically starts with 7656119)
     * - Custom Steam name only: customname (alphanumeric, hyphens, underscores)
     * @param {string} value - The value to validate.
     * @returns {boolean} - True if valid, false otherwise.
     */
    function isValidSteamInput(value) {
        if (!value || value.trim().length === 0) {
            return null;
        }

        const trimmedValue = value.trim();

        const fullProfileUrlPattern =
            /^https?:\/\/(www\.)?steamcommunity\.com\/profiles\/(7656119\d{10})\/?$/;
        const fullCustomUrlPattern =
            /^https?:\/\/(www\.)?steamcommunity\.com\/id\/([a-zA-Z0-9_-]+)\/?$/;
        const steamIdPattern = /^7656119\d{10}$/;
        const customNamePattern = /^[a-zA-Z0-9_-]+$/;

        if (
            fullProfileUrlPattern.test(trimmedValue) ||
            fullCustomUrlPattern.test(trimmedValue) ||
            steamIdPattern.test(trimmedValue) ||
            customNamePattern.test(trimmedValue)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Updates the validation state when steamID changes.
     */
    useEffect(() => {
        const validation = isValidSteamInput(steamID);
        setIsValidInput(validation);
        // Clear error when input becomes valid
        if (validation === true && error) {
            setError(null);
        }
    }, [steamID]);

    /**
     * Fetches the user data from the Steam API using the submitSteamID function.
     * @param {Event} event - The event object, used to prevent the default behavior.
     */
    async function getBasicData(event) {
        event.preventDefault();
        setIsLoading(true);
        setError(null);
        const csrfToken = document.querySelector(
            'meta[name="csrf-token"]'
        )?.content;

        await submitSteamID(steamID, csrfToken);
        setIsLoading(false);
    }

    /**
     * Submits the Steam ID to the API route to get the user data.
     * @param {string} steamID - The Steam ID to submit.
     * @param {string} csrfToken - The CSRF token to use for the request.
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
            }).then((response) => response.json());

            // Extract the first player object from the Steam API response
            const player = response.response?.players?.[0];

            if (!player) {
                throw new Error("No Steam user found with that ID or URL.");
            }

            // Put user data into new object and set state
            setUserData({ ...player });
        } catch (error) {
            console.error("Error fetching Steam data:", error);
            setError(error.message);
        }
    }

    return (
        <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-gray-100">
            {/* Background decoration */}
            <div className="fixed inset-0 overflow-hidden pointer-events-none">
                <div className="absolute top-0 left-1/4 w-96 h-96 bg-blue-600/10 rounded-full blur-3xl"></div>
                <div className="absolute bottom-0 right-1/4 w-96 h-96 bg-purple-600/10 rounded-full blur-3xl"></div>
            </div>

            <div className="flex flex-col justify-between min-h-screen relative z-10">
                {/* Header */}
                <header className="text-center pt-32 sm:pt-16 pb-8 px-4 animate-fade-in">
                    <h1 className="text-5xl sm:text-6xl md:text-7xl font-bold mb-4 bg-gradient-to-r from-blue-400 via-purple-400 to-blue-400 bg-clip-text text-transparent animate-gradient">
                        SteamGuessr
                    </h1>
                    <h2 className="text-lg sm:text-xl md:text-2xl text-gray-300 font-light">
                        Play fun minigames based on your Steam library
                    </h2>
                </header>

                {/* Main Content */}
                <main className="flex items-center justify-center px-4 py-8">
                    <Card className="space-y-6 animate-fade-in w-full max-w-2xl">
                        <div className="text-center space-y-2">
                            <h3 className="text-2xl font-semibold text-white">
                                Enter Your Steam Profile
                            </h3>
                            <p className="text-gray-400 text-sm">
                                Paste your Steam profile URL, Steam ID, or
                                custom ID to get started.{" "}
                                <button
                                    onClick={() => setIsHelpModalOpen(true)}
                                    className="text-blue-500 hover:underline"
                                >
                                    Need help?
                                </button>
                            </p>
                        </div>

                        <form className="space-y-4" onSubmit={getBasicData}>
                            <div className="relative">
                                <input
                                    className={`w-full px-4 py-3 bg-gray-800/50 border rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-1 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed ${
                                        isValidInput === false
                                            ? "border-red-500 focus:ring-red-500 focus:border-red-500"
                                            : isValidInput === true
                                            ? "border-green-500 focus:ring-green-500 focus:border-green-500"
                                            : "border-gray-700 focus:ring-blue-500 focus:border-transparent"
                                    }`}
                                    id="steam-id-input"
                                    type="text"
                                    value={steamID}
                                    required
                                    placeholder="https://steamcommunity.com/id/yourprofile"
                                    onChange={(event) =>
                                        setSteamID(event.target.value)
                                    }
                                    disabled={isLoading}
                                />
                            </div>

                            {error && (
                                <div className="p-3 bg-red-500/10 border border-red-500/30 rounded-lg text-red-400 text-sm">
                                    {error}
                                </div>
                            )}

                            <button
                                type="submit"
                                disabled={isLoading}
                                className="w-full py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white font-semibold rounded-lg transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none shadow-lg shadow-blue-500/25"
                            >
                                {isLoading ? (
                                    <span className="flex items-center justify-center">
                                        <svg
                                            className="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                        >
                                            <circle
                                                className="opacity-25"
                                                cx="12"
                                                cy="12"
                                                r="10"
                                                stroke="currentColor"
                                                strokeWidth="4"
                                            ></circle>
                                            <path
                                                className="opacity-75"
                                                fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                            ></path>
                                        </svg>
                                        Loading...
                                    </span>
                                ) : (
                                    "Get Started"
                                )}
                            </button>
                        </form>
                    </Card>
                </main>

                {/* Footer */}
                <footer className="py-8 px-4 text-center text-gray-500 text-sm">
                    © 2025{" "}
                    <a
                        href="https://github.com/Snevver"
                        target="_blank"
                        className="hover:underline"
                    >
                        Sven Hoeksema
                    </a>{" "}
                    &{" "}
                    <a
                        href="https://github.com/Penguin-09"
                        target="_blank"
                        className="hover:underline"
                    >
                        Son Bram van der Burg
                    </a>
                    . All rights reserved.
                </footer>
            </div>

            {/* Help Modal */}
            {isHelpModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <Card
                        className="flex flex-col gap-5 w-full max-w-2xl max-h-[90vh] overflow-y-auto break-words animate-fade-in"
                        transparency={90}
                    >
                        <h3 className="text-2xl font-semibold text-center">
                            Need help finding your Steam ID?
                        </h3>

                        <div className="space-y-1">
                            <p className="text-xl font-semibold">
                                Where to find your Steam ID:
                            </p>

                            <ul className="space-y-1">
                                <li>
                                    1. Open Steam and navigate to your profile
                                    page
                                </li>

                                <li>
                                    2. Click on the URL in the top left to copy
                                    it, or right click on your profile and
                                    select "Copy Page URL"
                                </li>

                                <li>
                                    3. Paste the URL into the input field and
                                    click "Get Started"
                                </li>
                            </ul>
                        </div>

                        <div className="space-y-1">
                            <p className="text-xl font-semibold">
                                You can use any of these formats:
                            </p>

                            <ul className="space-y-1">
                                <li>
                                    • Your full Steam profile URL:{" "}
                                    <span className="bg-gray-900/60 px-2 py-1 rounded-md font-mono break-all inline-block">
                                        https://steamcommunity.com/profiles/76561198000000000
                                    </span>
                                </li>

                                <li>
                                    • Your full custom Steam URL:{" "}
                                    <span className="bg-gray-900/60 px-2 py-1 rounded-md font-mono break-all inline-block">
                                        https://steamcommunity.com/id/customname
                                    </span>
                                </li>

                                <li>
                                    • Your Steam ID only:{" "}
                                    <span className="bg-gray-900/60 px-2 py-1 rounded-md font-mono break-all inline-block">
                                        76561198000000000
                                    </span>
                                </li>

                                <li>
                                    • Your custom Steam name only:{" "}
                                    <span className="bg-gray-900/60 px-2 py-1 rounded-md font-mono break-all inline-block">
                                        customname
                                    </span>
                                </li>
                            </ul>
                        </div>

                        <button
                            onClick={() => setIsHelpModalOpen(false)}
                            className="w-full py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white font-semibold rounded-lg transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none shadow-lg shadow-blue-500/25"
                        >
                            Got it!
                        </button>
                    </Card>
                </div>
            )}
        </div>
    );
}
