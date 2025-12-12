import React, { useEffect, useRef, useState } from "react";

export default function Admin() {
    const audioRef = useRef(null);
    const [duration, setDuration] = useState(0);
    const [started, setStarted] = useState(false);

    useEffect(() => {
        const audio = new Audio("/sounds/concrete.mp3");
        audio.preload = "metadata";
        audioRef.current = audio;

        const onLoaded = () => {
            if (audio.duration && audio.duration > 0)
                setDuration(audio.duration);
        };

        audio.addEventListener("loadedmetadata", onLoaded);

        const t = setTimeout(async () => {
            try {
                await audio.play();
                setStarted(true);
                if (audio.duration && audio.duration > 0)
                    setDuration(audio.duration);
            } catch (err) {
                console.warn(
                    "Audio autoplay blocked, waiting for user interaction to play.",
                    err
                );
                const onFirstClick = async () => {
                    try {
                        await audio.play();
                        setStarted(true);
                        if (audio.duration && audio.duration > 0)
                            setDuration(audio.duration);
                    } catch (e) {
                        console.warn("Play failed after user interaction", e);
                    }
                };
                window.addEventListener("click", onFirstClick, { once: true });
            }
        }, 0);

        audio._startTimer = t;

        return () => {
            if (audio._startTimer) clearTimeout(audio._startTimer);
            audio.pause();
            audio.removeEventListener("loadedmetadata", onLoaded);
        };
    }, []);

    const staticCss = `
        .admin-root {
            position: fixed;
            inset: 0;
            background-color: #000000;
            margin: 0;
            padding: 0;
            overflow: hidden;
            display: block;
        }

        .cat-image {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100vw;
            height: 100vh;
            object-fit: cover;
            transform: translate(-150%, -50%);
            z-index: 1000;
            pointer-events: none;
        }

        .fake-button {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 999;
            color: #ffffff;
            background: rgba(0,0,0,0.45);
            border: 1px solid rgba(255,255,255,0.25);
            padding: 14px 22px;
            font-size: 18px;
            border-radius: 8px;
            cursor: pointer;
            backdrop-filter: blur(4px);
        }

        .fake-button:hover {
            background: rgba(255,255,255,0.06);
            transform: translate(-50%, -50%) scale(1.02);
        }

        @keyframes slideInCat {
            from { transform: translate(-150%, -50%); }
            to   { transform: translate(-50%, -50%); }
        }
    `;

    const dynamicCss =
        started && duration > 0
            ? `
            .cat-image { animation: slideInCat ${duration}s linear forwards; }
        `
            : "";

    return (
        <div className="admin-root">
            <style>{staticCss + dynamicCss}</style>
            <button
                className="fake-button"
                onClick={(e) => {
                    e.preventDefault();
                }}
                aria-label="Go to dashboard"
            >
                Go to dashboard &rarr;
            </button>

            <img src="/images/cat-look.png" alt="cat" className="cat-image" />
        </div>
    );
}