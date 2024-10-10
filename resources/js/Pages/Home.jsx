import React, { useState, useEffect } from 'react';
import { Inertia } from '@inertiajs/inertia';
import { InertiaLink } from '@inertiajs/inertia-react';

const Home = ({ initialOperators }) => {
    const [operators, setOperators] = useState(initialOperators || []);
    const [selectedOperator, setSelectedOperator] = useState('');
    const [stops, setStops] = useState([]);
    const [selectedStop, setSelectedStop] = useState('');
    const [arrivals, setArrivals] = useState([]);

    useEffect(() => {
        // If no operators are provided initially, fetch them from the API
        if (operators.length === 0) {
            fetch('/operators')
                .then((response) => response.json())
                .then((data) => {
                    if (data) {
                        setOperators(data);
                    } else {
                        // If cache is not set, make API call and set cache
                        fetch('/api/operators')
                            .then((response) => response.json())
                            .then((apiData) => {
                                setOperators(apiData);
                            })
                            .catch((error) => console.error('Failed to fetch operators from API:', error));
                    }
                })
                .catch((error) => console.error('Failed to fetch operators:', error));
        }
    }, []);

    useEffect(() => {
        if (selectedOperator) {
            fetch(`/stops/${selectedOperator}`)
                .then((response) => response.json())
                .then((data) => {
                    if (data.Contents.dataObjects.ScheduledStopPoint) {
                        setStops(data.Contents.dataObjects.ScheduledStopPoint);
                    } else {
                        // If cache is not set, make API call and set cache
                        fetch(`/api/stops/${selectedOperator}`)
                            .then((response) => response.json())
                            .then((apiData) => {
                                setStops(apiData.Contents.dataObjects.ScheduledStopPoint || []);
                            })
                            .catch((error) => console.error('Failed to fetch stops from API:', error));
                    }
                })
                .catch((error) => console.error('Failed to fetch stops:', error));
        }
    }, [selectedOperator]);

    useEffect(() => {
        if (selectedStop && selectedOperator) {
            fetch(`/real-time-arrivals/${selectedStop}/${selectedOperator}`)
                .then((response) => response.json())
                .then((data) => {
                    if (data.ServiceDelivery && data.ServiceDelivery.StopMonitoringDelivery.MonitoredStopVisit) {
                        setArrivals(data.ServiceDelivery.StopMonitoringDelivery.MonitoredStopVisit);
                    } else {
                        setArrivals([]);
                    }
                })
                .catch((error) => console.error('Failed to fetch real-time arrivals:', error));
        }
    }, [selectedStop, selectedOperator]);

    const handleOperatorChange = (e) => {
        setSelectedOperator(e.target.value);
        setSelectedStop(''); // Reset the selected stop when a new operator is selected
        setArrivals([]); // Reset arrivals when operator changes
    };

    const handleStopChange = (e) => {
        setSelectedStop(e.target.value);
    };

    return (
        <div className="container mx-auto p-4">
            <h1 className="text-2xl font-bold mb-4">Select a Transit Operator</h1>
            <div className="mb-4">
                <label htmlFor="operator" className="block text-lg mb-2">Choose an Operator:</label>
                <select
                    id="operator"
                    value={selectedOperator}
                    onChange={handleOperatorChange}
                    className="p-2 border rounded w-full"
                >
                    <option value="">Select an operator...</option>
                    {operators.map((operator) => (
                        <option key={operator.Id} value={operator.Id}>
                            {operator.Name}
                        </option>
                    ))}
                </select>
            </div>
            {selectedOperator && (
                <div className="mb-4">
                    <label htmlFor="stop" className="block text-lg mb-2">Choose a Stop:</label>
                    <select
                        id="stop"
                        value={selectedStop}
                        onChange={handleStopChange}
                        className="p-2 border rounded w-full"
                    >
                        <option value="">Select a stop...</option>
                        {stops.map((stop) => (
                            <option key={stop.id} value={stop.id}>
                                {stop.Name}
                            </option>
                        ))}
                    </select>
                </div>
            )}
            {arrivals.length > 0 && (
                <div className="mt-4">
                    <h2 className="text-xl font-bold mb-2">Real-Time Arrivals</h2>
                    <ul className="list-disc pl-5">
                        {arrivals.map((arrival, index) => (
                            <li key={index} className="mb-2">
                                <strong>Line {arrival.MonitoredVehicleJourney.LineRef}:</strong> Expected Arrival Time - {arrival.MonitoredVehicleJourney.MonitoredCall.ExpectedArrivalTime}
                            </li>
                        ))}
                    </ul>
                </div>
            )}
        </div>
    );
};

export default Home;
