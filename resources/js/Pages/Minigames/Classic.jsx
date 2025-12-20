import React, { useState, useEffect } from "react";
import Layout from "../../Layouts/Layout";
import Modal from "../../Components/Modal";

export default function Classic() {
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);
    const [gameData, setGameData] = useState(null);
    const [swipeOut, setSwipeOut] = useState(false);
    const [isModalOpen, setIsModalOpen] = useState(false);

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
                    <div>
                        <h1>{gameData.game.name}</h1>
                    </div>

                    <Modal
                        isOpen={isModalOpen}
                        onClose={() => setIsModalOpen(false)}
                        title="How does this work?"
                    >
                        <p>
                            Welcome to SteamGuessr Classic!
                        </p>

                        <p>
                            test
                        </p>
                    </Modal>
                </>
            )}
        </Layout>
    );
}
