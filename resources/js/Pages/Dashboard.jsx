import React, { useState, useEffect } from "react";
import { usePage } from "@inertiajs/react";
import Layout from "../Layouts/Layout";
import Card from "../Components/Card";

export default function Dashboard() {
    const { steam } = usePage().props;
    const [swipeOut] = useState(false);

    // useEffect(() => {
    //     console.log("steam data:", steam);
    // }, [steam]);

    // Process some stats before they are displayed
    const playedPercentage = Math.round(steam.playedPercentage * 100);

    const formatCreationDate = (timeCreated) => {
        if (!timeCreated) return "Unknown";
        const { year, month, day } = timeCreated;
        return `${day}-${month}-${year}`;
    };

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

                        <div className="flex justify-center flex-wrap gap-2 mt-2">
                            <span className="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-500/10 text-blue-300 border border-blue-500/30">
                                {steam.personaState}
                            </span>

                            <span className="inline-flex text-center items-center px-2.5 py-1 rounded-full text-xs font-medium bg-purple-500/10 text-purple-300 border border-purple-500/30">
                                Created on {formatCreationDate(steam.timeCreated)}
                            </span>
                        </div>
                    </div>
                </div>

                {/* Stats grid */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <Card className="p-5 bg-gray-900/50 border-gray-700/70">
                        <p className="text-xs uppercase tracking-wide text-gray-400">
                            Total games
                        </p>

                        <p className="mt-2 text-2xl font-semibold text-white">
                            {steam.totalGamesOwned}
                        </p>
                    </Card>

                    <Card className="p-5 bg-gray-900/50 border-gray-700/70">
                        <p className="text-xs uppercase tracking-wide text-gray-400">
                            Total playtime
                        </p>

                        <p className="mt-2 text-2xl font-semibold text-white">
                            {steam.totalPlaytimeHours}

                            <span className="ml-1 text-sm text-gray-400">
                                hours
                            </span>
                        </p>
                    </Card>

                    <Card className="p-5 bg-gray-900/50 border-gray-700/70">
                        <p className="text-xs uppercase tracking-wide text-gray-400">
                            Average playtime
                        </p>

                        <p className="mt-2 text-2xl font-semibold text-white">
                            {steam.averagePlaytimeHours}

                            <span className="ml-1 text-sm text-gray-400">
                                hours
                            </span>
                        </p>
                    </Card>

                    <Card className="p-5 bg-gray-900/50 border-gray-700/70">
                        <p className="text-xs uppercase tracking-wide text-gray-400">
                            Games played
                        </p>

                        <p className="mt-2 text-2xl font-semibold text-white">
                            {playedPercentage}

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