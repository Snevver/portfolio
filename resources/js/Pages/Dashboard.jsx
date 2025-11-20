import React, { useState, useEffect } from "react";
import Layout from "../Layouts/Layout";
import Card from "../Components/Card";
import Skeleton from "../Components/Skeleton";

export default function Dashboard() {
    const [isLoading, setIsLoading] = useState(true);

    return (
        <Layout>
            {/* User Summary */}
            <Card>

            </Card>
        </Layout>
    );
}
