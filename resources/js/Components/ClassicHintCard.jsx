import React from "react";
import Card from "./Card";

export default function ClassicHintCard({ difficulty = "hard", hintData }) {
    if (!hintData) {
        return null;
    }

    switch (hintData.hint_name) {
        // EASY HINTS
        case "first_letter":
            return (
                <Card>
                    <p>{difficulty}</p>
                    <p>First letter: {hintData.data.first_letter}</p>
                </Card>
            );
        case "developer_publisher":
            return (
                <Card>
                    <p>{difficulty}</p>
                    <p>Developer: {hintData.data.developer}</p>
                    <p>Publisher: {hintData.data.publisher}</p>
                </Card>
            );
        case "tags":
            return (
                <Card>
                    <p>{difficulty}</p>
                    <p>
                        Tags:{" "}
                        {Array.isArray(hintData.data.tags)
                            ? hintData.data.tags.join(", ")
                            : hintData.data.tags}
                    </p>
                </Card>
            );
        case "scrambled_name":
            return (
                <Card>
                    <p>{difficulty}</p>
                    <p>Scrambled name: {hintData.data.scrambled_name}</p>
                </Card>
            );

        // MEDIUM HINTS
        case "total_playtime":
            return (
                <Card>
                    <p>{difficulty}</p>
                    <p>Total playtime: {hintData.data.playtime} minutes</p>
                </Card>
            );
        case "release_date":
            return (
                <Card>
                    <p>{difficulty}</p>
                    <p>Release date: {hintData.data.release_date}</p>
                </Card>
            );
        case "blurred_banner":
            return (
                <Card>
                    <p>{difficulty}</p>
                    <p className="mb-2">Blurred banner:</p>
                    <img
                        src={hintData.data.banner_url}
                        alt="Blurred game banner"
                        className="w-full rounded-lg blur-2xl"
                    />
                </Card>
            );

        // HARD HINTS
        case "reviews":
            return (
                <Card>
                    <p>{difficulty}</p>
                    <p>
                        Review ratio: {hintData.data.review_ratio} (
                        {hintData.data.total_reviews} reviews)
                    </p>
                </Card>
            );
        case "required_space":
            return (
                <Card>
                    <p>{difficulty}</p>
                    <p>
                        Required disk space: {hintData.data.required_disk_space}
                    </p>
                </Card>
            );
        case "last_played":
            return (
                <Card>
                    <p>{difficulty}</p>
                    <p>Last played: {hintData.data.last_played}</p>
                </Card>
            );
        case "player_counts":
            return (
                <Card>
                    <p>{difficulty}</p>
                    <p>Total owners: {hintData.data.total_owners}</p>
                    <p>Current players: {hintData.data.current_players}</p>
                </Card>
            );

        default:
            return (
                <Card>
                    <p>{difficulty}</p>
                    <p>No hint data</p>
                </Card>
            );
    }
}
