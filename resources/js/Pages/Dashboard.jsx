import React from 'react';
import { usePage } from '@inertiajs/react';
import Layout from "../Layouts/Layout";
import Card from "../Components/Card";

export default function Dashboard() {
    const { steam } = usePage().props;

    return (
        <Layout>
            <Card>
                
            </Card>
        </Layout>
    );
}