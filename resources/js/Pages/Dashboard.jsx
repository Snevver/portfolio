import React, { useState } from "react";
import { usePage } from "@inertiajs/react";
import Layout from "../Layouts/Layout";
import Card from "../Components/Card";

export default function Dashboard() {
    const { steam } = usePage().props;
    const [swipeOut, setSwipeOut] = useState(false);

    return (
        <Layout swipeOut={swipeOut}>
            <Card className="w-xl"></Card>
        </Layout>
    );
}
