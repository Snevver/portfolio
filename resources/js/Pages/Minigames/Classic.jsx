import React, { useState, useEffect } from "react";
import { usePage } from "@inertiajs/react";
import Layout from "../../Layouts/Layout";
import Modal from "../../Components/Modal";
import Card from "../../Components/Card";
import Button from "../../Components/Button";
import ClassicHintCard from "../../Components/ClassicHintCard";

export default function Classic() {
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);
    const [gameData, setGameData] = useState(null);
    const [swipeOut, setSwipeOut] = useState(false);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [guess, setGuess] = useState("");
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [showSuggestions, setShowSuggestions] = useState(false);
    const [selectedIndex, setSelectedIndex] = useState(-1);
    const [hintStage, setHintStage] = useState("hard");
    const [isOver, setIsOver] = useState(false);
    const { steam } = usePage().props;

    // NOTE: AI helped with the autocomplete functionality in this file.

    // Filter games based on input
    const filteredGames = React.useMemo(() => {
        if (!guess.trim() || !steam?.allGames) {
            return [];
        }

        const searchTerm = guess.toLowerCase().trim();

        return steam.allGames
            .filter((game) => game.name.toLowerCase().includes(searchTerm))
            .slice(0, 10);
    }, [guess, steam?.allGames]);

    // Fetches a random game and hints
    useEffect(() => {
        fetch("/api/classic")
            .then((response) => response.json())
            .then((data) => {
                setGameData(data);
                console.log(data);
            })
            .catch((error) => setError(error))
            .finally(() => {
                setIsLoading(false);
                setIsModalOpen(true);
            });
    }, []);

    return (
        <Layout isLandingPage={false} swipeOut={swipeOut}>
            {isLoading ? (
                <div className="flex items-center">
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

                    <p className="text-white text-lg">Hang on...</p>
                </div>
            ) : (
                <>
                    {isOver ? (
                        <>
                            {/* Win Card */}
                            <Card>
                                <p>The game was {gameData?.game?.name}</p>
                            </Card>
                        </>
                    ) : (
                        <>
                            {/* Hint Card */}
                            <ClassicHintCard
                                difficulty={hintStage}
                                hintData={gameData?.hints_data[hintStage]}
                            />
                        </>
                    )}

                    {/* Input Card */}
                    <Card className="space-y-6 w-full max-w-2xl">
                        <div className="text-center space-y-2">
                            <h3 className="text-2xl font-semibold text-white">
                                Enter Your Guess
                            </h3>

                            <p className="text-gray-400 text-sm">
                                Type the name of the game you think matches the
                                hint above.
                            </p>
                        </div>

                        <form
                            className="space-y-4"
                            onSubmit={(event) => {
                                event.preventDefault();

                                if (!gameData?.game?.name || isOver) {
                                    return;
                                }

                                const difficultyOrder = [
                                    "hard",
                                    "medium",
                                    "easy",
                                ];
                                const normalizedGuess = guess
                                    .trim()
                                    .toLowerCase();
                                const correctName = gameData.game.name
                                    .trim()
                                    .toLowerCase();

                                if (!normalizedGuess) {
                                    return;
                                }

                                // Correct guess -> game over
                                if (normalizedGuess === correctName) {
                                    setIsOver(true);
                                    return;
                                }

                                // Wrong guess -> advance hint or end game if on last hint
                                const currentIndex =
                                    difficultyOrder.indexOf(hintStage);

                                if (
                                    currentIndex === -1 ||
                                    currentIndex === difficultyOrder.length - 1
                                ) {
                                    // Already on last hint (easy) -> game over
                                    setIsOver(true);
                                } else {
                                    setHintStage(
                                        difficultyOrder[currentIndex + 1]
                                    );
                                }
                            }}
                        >
                            <div className="relative">
                                <input
                                    className="w-full px-4 py-3 bg-gray-800/50 border rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-1 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed border-gray-700 focus:ring-blue-500 focus:border-transparent"
                                    id="guess-input"
                                    type="text"
                                    value={guess}
                                    required
                                    placeholder="Enter game name..."
                                    onChange={(event) => {
                                        setGuess(event.target.value);
                                        setShowSuggestions(true);
                                        setSelectedIndex(-1);
                                    }}
                                    onFocus={() => {
                                        if (filteredGames.length > 0) {
                                            setShowSuggestions(true);
                                        }
                                    }}
                                    onBlur={() => {
                                        // Delay to allow click events to fire
                                        setTimeout(
                                            () => setShowSuggestions(false),
                                            200
                                        );
                                    }}
                                    onKeyDown={(e) => {
                                        if (filteredGames.length === 0) return;

                                        if (e.key === "ArrowDown") {
                                            e.preventDefault();
                                            setSelectedIndex((prev) =>
                                                prev < filteredGames.length - 1
                                                    ? prev + 1
                                                    : prev
                                            );
                                        } else if (e.key === "ArrowUp") {
                                            e.preventDefault();
                                            setSelectedIndex((prev) =>
                                                prev > 0 ? prev - 1 : -1
                                            );
                                        } else if (
                                            e.key === "Enter" &&
                                            selectedIndex >= 0
                                        ) {
                                            e.preventDefault();
                                            setGuess(
                                                filteredGames[selectedIndex]
                                                    .name
                                            );
                                            setShowSuggestions(false);
                                            setSelectedIndex(-1);
                                        } else if (
                                            e.key === "Tab" &&
                                            selectedIndex >= 0
                                        ) {
                                            e.preventDefault();
                                            setGuess(
                                                filteredGames[selectedIndex]
                                                    .name
                                            );
                                            setShowSuggestions(false);
                                            setSelectedIndex(-1);
                                        } else if (e.key === "Escape") {
                                            setShowSuggestions(false);
                                            setSelectedIndex(-1);
                                        }
                                    }}
                                    disabled={isSubmitting || isOver}
                                />
                                {showSuggestions &&
                                    filteredGames.length > 0 && (
                                        <div className="absolute z-10 w-full mt-1 bg-gray-800/95 backdrop-blur-sm border border-gray-700 rounded-lg shadow-2xl max-h-60 overflow-y-auto">
                                            {filteredGames.map(
                                                (game, index) => (
                                                    <button
                                                        key={game.id}
                                                        type="button"
                                                        className={`w-full text-left px-4 py-2 hover:bg-gray-700/50 transition-colors ${
                                                            index ===
                                                            selectedIndex
                                                                ? "bg-gray-700/50"
                                                                : ""
                                                        }`}
                                                        onClick={() => {
                                                            setGuess(game.name);
                                                            setShowSuggestions(
                                                                false
                                                            );
                                                            setSelectedIndex(
                                                                -1
                                                            );
                                                        }}
                                                        onMouseEnter={() =>
                                                            setSelectedIndex(
                                                                index
                                                            )
                                                        }
                                                    >
                                                        <span className="text-white">
                                                            {game.name}
                                                        </span>
                                                    </button>
                                                )
                                            )}
                                        </div>
                                    )}
                            </div>

                            {error && (
                                <div className="p-3 bg-red-500/10 border border-red-500/30 rounded-lg text-red-400 text-sm">
                                    {error}
                                </div>
                            )}

                            <Button
                                type="submit"
                                disabled={isSubmitting || isOver}
                                ariaLabel="Submit Guess"
                            >
                                {isSubmitting ? (
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
                                        Hold on...
                                    </span>
                                ) : (
                                    "Submit Guess"
                                )}
                            </Button>
                        </form>
                    </Card>

                    {/* Tutorial Modal */}
                    <Modal
                        isOpen={false}
                        onClose={() => setIsModalOpen(false)}
                        title="Welcome to SteamGuessr Classic!"
                    >
                        <div className="space-y-2">
                            <p className="text-xl font-semibold text-white">
                                How does this work?
                            </p>

                            <p>
                                The objective of this minigame is to guess the
                                correct game based on the hints provided.
                            </p>

                            <p>
                                You can guess as many times as you want, but you
                                only get three hints; hard, medium and easy.
                            </p>

                            <p>
                                After you guess the correct game, you will get a
                                score based on how many hints you used.
                            </p>
                        </div>
                    </Modal>
                </>
            )}
        </Layout>
    );
}
