import React, { useState, useEffect } from "react";
import Layout from "../Layouts/Layout";
import Card from "../Components/Card";

export default function Landing() {
    const [steamID, setSteamID] = useState("");
    const [isCustomID, setIsCustomID] = useState(false);
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
        const trimmedValue = value.trim();

        if (!value || trimmedValue.length === 0) {
            return null;
        }

        if (trimmedValue.length > 3) {
            if (
                /^7656119\d{10}$/.test(trimmedValue) ||
                /^https?:\/\/(www\.)?steamcommunity\.com\/profiles\/(7656119\d{10})\/?$/.test(
                    trimmedValue
                )
            ) {
                // Numeric ID
                setIsCustomID(false);
                return true;
            } else if (
                /^[a-zA-Z0-9_-]+$/.test(trimmedValue) ||
                /^https?:\/\/(www\.)?steamcommunity\.com\/id\/([a-zA-Z0-9_-]+)\/?$/.test(
                    trimmedValue
                )
            ) {
                // Custom ID
                setIsCustomID(true);
                return true;
            } else {
                return false;
            }
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
    }, [steamID, error]);

    /**
     * Handles ESC key press to close the help modal and prevents body scroll when modal is open.
     */
    useEffect(() => {
        if (isHelpModalOpen) {
            // Prevent body scroll when modal is open
            document.body.style.overflow = "hidden";

            // Handle ESC key press
            const handleEscape = (event) => {
                if (event.key === "Escape") {
                    setIsHelpModalOpen(false);
                }
            };

            document.addEventListener("keydown", handleEscape);

            return () => {
                document.body.style.overflow = "";
                document.removeEventListener("keydown", handleEscape);
            };
        }
    }, [isHelpModalOpen]);

    /**
     * Submits the Steam ID to the API route to check if the Steam ID is valid.
     * @param {Event} event - The event object, used to prevent the default behavior.
     */
    async function submitSteamID(event) {
        event.preventDefault();
        setIsLoading(true);
        setError(null);

        const csrfToken = document.querySelector(
            'meta[name="csrf-token"]'
        )?.content;

        try {
            // Send POST request to the API route to check if the Steam ID is valid
            const response = await fetch("/validate-user", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({ steamID, isCustomID }),
            }).then((response) => response.json());

            if (response.data.success) {
                console.log(
                    "Success, user can now be redirected to the dashboard"
                );
            } else {
                setError(response.data.message);
            }
        } catch (error) {
            console.error("Error submitting Steam ID:", error);
            setError(error.message);
        }

        setIsLoading(false);
    }

    return (
        <Layout>
            <Card className="space-y-6 animate-fade-in w-full max-w-2xl">
                <div className="text-center space-y-2">
                    <h3 className="text-2xl font-semibold text-white">
                        Enter Your Steam Profile
                    </h3>
                    <p className="text-gray-400 text-sm">
                        Paste your Steam profile URL, Steam ID, or custom ID to
                        get started.{" "}
                        <button
                            onClick={() => setIsHelpModalOpen(true)}
                            className="text-blue-500 hover:underline"
                        >
                            Need help?
                        </button>
                    </p>
                </div>

                <form className="space-y-4" onSubmit={submitSteamID}>
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
                            onChange={(event) => setSteamID(event.target.value)}
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

            {/* Help Modal */}
            {isHelpModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
                    {/* Backdrop */}
                    <div
                        className="fixed inset-0 bg-black/50 backdrop-blur-sm"
                        aria-hidden="true"
                        onClick={() => setIsHelpModalOpen(false)}
                    ></div>

                    <Card
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="help-modal-title"
                        className="flex flex-col gap-5 w-full max-w-2xl max-h-[90vh] overflow-y-auto break-words animate-fade-in relative z-10"
                        transparency={95}
                        onClick={(e) => {
                            // Prevent modal from closing when clicking inside the card
                            e.stopPropagation();
                        }}
                    >
                        <h3
                            id="help-modal-title"
                            className="text-2xl font-semibold text-center"
                        >
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
                            aria-label="Close help modal"
                            className="w-full py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white font-semibold rounded-lg transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none shadow-lg shadow-blue-500/25"
                        >
                            Got it!
                        </button>
                    </Card>
                </div>
            )}
        </Layout>
    );
}
