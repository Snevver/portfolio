import React from "react";
import Card from "./Card";
import { CircleQuestionMark } from "lucide-react";

export default function MinigameCard({
    useLogo = false,
    icon = CircleQuestionMark,
    title = "Error",
    description = "Error",
    disabled = false,
    ...props
}) {
    const IconComponent = icon;

    return (
        <Card
            className="flex flex-col gap-1 bg-gray-900/50 border-gray-700/70 hover:bg-gray-900/70 hover:cursor-pointer hover:scale-[1.02] active:scale-[0.98] transition-all duration-200"
            {...props}
        >
            <div className="flex items-center gap-3">
                {useLogo && (
                    <img
                        src="/images/logo-white.png"
                        alt={`${title} logo`}
                        className="text-2xl w-[1em] h-[1em]"
                    />
                )}

                {!useLogo && <IconComponent className="text-2xl" />}
                <h1 className="text-2xl font-semibold text-white">{title}</h1>
            </div>

            <p className="text-sm text-gray-400">{description}</p>
        </Card>
    );
}
