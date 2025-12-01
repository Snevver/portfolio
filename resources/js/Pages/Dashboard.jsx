import React, { useState, useEffect } from "react";
import { usePage } from "@inertiajs/react";
import CountUp from "react-countup";
import Layout from "../Layouts/Layout";
import Card from "../Components/Card";

export default function Dashboard() {
    const { steam } = usePage().props;
    const [swipeOut] = useState(false);

    // !!! Debugging. Remove before deployment.
    useEffect(() => {
        console.log("steam data:", steam);
    }, [steam]);

    /**
     * Converts the playtime in minutes to hours if needed.
     * @param {number} playtimeInMinutes - The playtime in minutes.
     * @returns {number} The playtime in hours if needed, otherwise the playtime in minutes.
     */
    const playtimeConversion = (playtimeInMinutes) => {
        if (playtimeInMinutes >= 60) {
            return Math.floor(playtimeInMinutes / 60);
        } else {
            return playtimeInMinutes;
        }
    };

    // Color the persona state badge
    const personaStateClasses = {
        Offline: "bg-gray-500/10 text-gray-300 border-gray-500/30",
        Online: "bg-green-500/10 text-green-300 border-green-500/30",
        Busy: "bg-red-500/10 text-red-300 border-red-500/30",
        Away: "bg-yellow-500/10 text-yellow-300 border-yellow-500/30",
    };

    const personaStateBadgeClass =
        personaStateClasses[steam.personaState] ||
        "bg-blue-500/10 text-blue-300 border-blue-500/30";

    return (
        <Layout swipeOut={swipeOut}>
            <Card className="flex flex-col w-full max-w-4xl mx-auto gap-8">
                {/* Profile header */}
                <div className="flex flex-col sm:flex-row justify-center items-center gap-6">
                    <div className="relative">
                        <img
                            src={steam.profilePictureURL}
                            alt="Steam Profile Picture"
                            className="w-24 h-24 rounded-2xl border border-gray-700 shadow-lg object-cover"
                        />
                    </div>

                    <div className="space-y-1">
                        <h1 className="text-2xl text-center sm:text-left font-semibold text-white">
                            {steam.username}
                        </h1>

                        <p className="text-sm text-center sm:text-left text-gray-400 font-mono break-all">
                            Steam ID:{" "}
                            <span className="text-gray-300">
                                {steam.steamID}
                            </span>
                        </p>

                        <div className="flex justify-center sm:justify-start flex-wrap gap-2 mt-2">
                            <span
                                className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border ${personaStateBadgeClass}`}
                            >
                                {steam.personaState}
                            </span>

                            <span className="inline-flex text-center items-center px-2.5 py-1 rounded-full text-xs font-medium bg-purple-500/10 text-purple-300 border border-purple-500/30">
                                Created on {steam.timeCreated || "Unknown"}
                            </span>
                        </div>
                    </div>
                </div>

                {/* Stats grid */}
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-5">
                    <Card className="p-5 bg-gray-900/50 border-gray-700/70">
                        <p className="text-xs uppercase tracking-wide text-gray-400">
                            Total games
                        </p>

                        <p className="mt-2 text-2xl font-semibold text-white">
                            <CountUp
                                end={steam.totalGamesOwned}
                                duration={1}
                                start={0}
                            />
                        </p>
                    </Card>

                    <Card className="p-5 bg-gray-900/50 border-gray-700/70">
                        <p className="text-xs uppercase tracking-wide text-gray-400">
                            Total playtime
                        </p>

                        <p className="mt-2 text-2xl font-semibold text-white">
                            <CountUp
                                end={playtimeConversion(
                                    steam.totalPlaytimeMinutes
                                )}
                                duration={1}
                                start={0}
                            />

                            <span className="ml-1 text-sm text-gray-400">
                                {playtimeConversion(
                                    steam.totalPlaytimeMinutes
                                ) > 1
                                    ? "hours"
                                    : "minutes"}
                            </span>
                        </p>
                    </Card>

                    <Card className="p-5 bg-gray-900/50 border-gray-700/70">
                        <p className="text-xs uppercase tracking-wide text-gray-400">
                            Average playtime
                        </p>

                        <p className="mt-2 text-2xl font-semibold text-white">
                            <CountUp
                                end={playtimeConversion(
                                    steam.averagePlaytimeMinutes
                                )}
                                duration={1}
                                start={0}
                            />

                            <span className="ml-1 text-sm text-gray-400">
                                {playtimeConversion(
                                    steam.averagePlaytimeMinutes
                                ) > 1
                                    ? "hours"
                                    : "minutes"}
                            </span>
                        </p>
                    </Card>

                    <Card className="p-5 bg-gray-900/50 border-gray-700/70">
                        <p className="text-xs uppercase tracking-wide text-gray-400">
                            Games played
                        </p>

                        <p className="mt-2 text-2xl font-semibold text-white">
                            <CountUp
                                end={steam.playedPercentage}
                                decimals={2}
                                duration={1}
                                start={0}
                            />

                            <span className="ml-1 text-sm text-gray-400">
                                %
                            </span>
                        </p>
                    </Card>
                </div>
            </Card>
        </Layout>
    );
}
