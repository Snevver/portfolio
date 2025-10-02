import React from 'react';
import { Text, View } from 'react-native-web';

export default function Welcome() {
    return (
        <View style={{ 
            minHeight: '100vh', 
            display: 'flex', 
            alignItems: 'center', 
            justifyContent: 'center', 
            backgroundColor: '#000', 
            backgroundImage: 'url(/images/cat-look.png)', 
            backgroundRepeat: 'no-repeat', 
            backgroundSize: 'cover',
            backgroundPosition: 'center',
            position: 'relative'
        }}>
            <View style={{ textAlign: 'center', backgroundColor: 'rgba(0, 0, 0, 0.6)', padding: 32, borderRadius: 16, backdropFilter: 'blur(8px)' }}>
                <Text style={{ fontSize: 48, fontWeight: 'bold', color: '#ffffff', marginBottom: 16 }}>
                    Son ik zie je
                </Text>
                <Text style={{ fontSize: 20, color: '#f3f4f6', textAlign: 'center' }}>
                    Je bent nooit veilig
                </Text>
                <Text style={{ fontSize: 10, color: '#f3f4f6', textAlign: 'center' }}>
                    Ik ga je aanraken
                </Text>
            </View>
        </View>
    );
}