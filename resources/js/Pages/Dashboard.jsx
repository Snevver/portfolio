import React, { useState, useEffect } from "react";
import { usePage } from "@inertiajs/react";
import Layout from "../Layouts/Layout";
import Card from "../Components/Card";

export default function Dashboard() {
    const { steam } = usePage().props;
    const [swipeOut] = useState(false);

    // Uncomment to debug / inspect available steam props
    // useEffect(() => {
    //     console.log('Available steam props:', steam);
    // }, []);

    return (
        <Layout swipeOut={swipeOut}>
            <Card className="w-xl"></Card>
        </Layout>
    );
}