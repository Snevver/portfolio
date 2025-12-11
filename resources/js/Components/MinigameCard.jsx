import React from "react";
import Card from "./Card";
import { CircleQuestionMark, Hammer } from "lucide-react";

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
        <a>
            <Card
                className={`relative flex flex-col gap-1 bg-gray-900/50 border-gray-700/70 transition-all duration-200 ${
                    disabled
                        ? "cursor-not-allowed"
                        : "cursor-pointer hover:bg-gray-900/70 hover:scale-[1.02] active:scale-[0.98]"
                }`}
                {...props}
            >
                <div
                    className={`flex items-center gap-3 ${
                        disabled ? "blur-sm" : ""
                    }`}
                >
                    {useLogo && (
                        <img
                            src="/images/logo-white.png"
                            alt={`${title} logo`}
                            className="w-6 h-6"
                        />
                    )}

                    {!useLogo && <IconComponent className="text-2xl" />}
                    <h1 className="text-2xl font-semibold text-white">
                        {title}
                    </h1>
                </div>

                <p
                    className={`text-sm text-gray-400 ${
                        disabled ? "blur-sm" : ""
                    }`}
                >
                    {description}
                </p>

                {disabled && (
                    <div className="absolute inset-0 flex flex-col items-center justify-center bg-black/60 rounded-xl z-10">
                        <Hammer className="w-4 h-4 mb-2 text-white" />
                        <p className="text-sm text-white">Coming soon...</p>
                    </div>
                )}
            </Card>
        </a>
    );
}
