import React, { useState, useEffect } from "react";
import Layout from "../Layouts/Layout";

export default function Classic() {
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState(null);
    const [swipeOut, setSwipeOut] = useState(false);

    return (
        <Layout isLandingPage={false} swipeOut={swipeOut}>

        </Layout>
    );
}
