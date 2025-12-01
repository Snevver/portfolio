import React, { useState } from "react";
import { ChevronLeft } from "lucide-react";

export default function Layout({
    children,
    isLandingPage = false,
    swipeOut = false,
}) {
    const [showBackButton, setShowBackButton] = useState(false);

    // Wait 0.3 seconds before showing the back button as to not disrupt the swipe animation
    setTimeout(() => {
        setShowBackButton(true);
    }, 300);

    return (
        <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-gray-100 overflow-x-hidden">
            {
                /* Back button */
                showBackButton && !isLandingPage && (
                    <button
                        className="absolute top-4 left-4 text-gray-300 hover:scale-110 active:scale-95 transition-all duration-200 z-30 animate-pop-in"
                        onClick={() => {
                            // Redirect to the previous page
                            setTimeout(() => {
                                window.history.back();
                            }, 300);
                        }}
                        aria-label="Go back"
                    >
                        <ChevronLeft />
                    </button>
                )
            }

            {/* Background decoration */}
            <div className="fixed inset-0 overflow-hidden pointer-events-none">
                <div className="absolute top-0 left-1/4 w-96 h-96 bg-blue-600/10 rounded-full blur-3xl"></div>
                <div className="absolute bottom-0 right-1/4 w-96 h-96 bg-purple-600/10 rounded-full blur-3xl"></div>
            </div>

            <div className="flex flex-col justify-between min-h-screen relative z-10">
                {/* Header */}
                <header
                    className={`text-center pt-16 pb-8 px-4 ${
                        isLandingPage ? "animate-fade-in" : ""
                    }`}
                >
                    <h1 className="text-5xl sm:text-6xl md:text-7xl font-bold mb-4 bg-gradient-to-r from-blue-400 via-purple-400 to-blue-400 bg-clip-text text-transparent animate-title-gradient">
                        SteamGuessr
                    </h1>
                    <h2 className="text-lg sm:text-xl md:text-2xl text-gray-300 font-light">
                        Play fun minigames based on your Steam library
                    </h2>
                </header>

                {/* Main Content */}
                <main
                    className={`flex flex-col items-center justify-center w-full px-4 py-8 ${
                        swipeOut
                            ? "animate-swipe-out"
                            : isLandingPage
                            ? ""
                            : "animate-swipe-in"
                    }`}
                >
                    {children}
                </main>

                {/* Footer */}
                <footer className="py-8 px-4 text-center text-gray-500 text-sm">
                    © 2025{" "}
                    <a
                        href="https://github.com/Snevver"
                        target="_blank"
                        rel="noopener noreferrer"
                        className="hover:underline"
                    >
                        Sven Hoeksema
                    </a>{" "}
                    &{" "}
                    <a
                        href="https://github.com/Penguin-09"
                        target="_blank"
                        rel="noopener noreferrer"
                        className="hover:underline"
                    >
                        Son Bram van der Burg
                    </a>
                    . All rights reserved.
                </footer>
            </div>
        </div>
    );
}
