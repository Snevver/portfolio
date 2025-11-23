import React, { useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import Layout from "../Layouts/Layout";
import Card from "../Components/Card";

export default function Dashboard() {
    const { steam } = usePage().props;
    
    useEffect(() => {
        console.log('Steam data:', steam);
    }, []);

    return (
        <Layout>
            <Card>
                {steam.username ? (
                    <div>
                        <img src={steam.profileURL} alt="Profile" />
                        <h2>{steam.username}</h2>
                        <p>Steam ID: {steam.steamID}</p>
                        <p>Total games: {steam.totalGamesOwned}</p>
                    </div>
                ) : (
                    <p>No Steam profile connected</p>
                )}
            </Card>
        </Layout>
    );
}