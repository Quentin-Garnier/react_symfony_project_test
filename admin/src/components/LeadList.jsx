import React, { useEffect, useState } from 'react';
import Button from './Button'; // Assurez-vous que le chemin est correct
import 'bootstrap/dist/css/bootstrap.min.css';
import listURL from '../url';

const LeadList = () => {
    const [leads, setLeads] = useState([]);
    const [filteredLeads, setFilteredLeads] = useState([]);
    const [error, setError] = useState(null);
    const [filter, setFilter] = useState('');
    const [id, setId] = useState("");
    const [dateRangeStart, setDateRangeStart] = useState("");
    const [dateRangeEnd, setDateRangeEnd] = useState("");

    useEffect(() => {
        const fetchLeads = async () => {
            try {
                const response = await fetch(listURL.recupLead, {
                    method: 'GET',
                    mode: 'cors',
                }); // Changez l'URL si nécessaire
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                setLeads(data); // Assurez-vous que le format des données correspond à ce que vous attendez
                setFilteredLeads(data);
            } catch (err) {
                setError(err.message);
            }
        };

        fetchLeads();
    }, []);

    const filterLeads = (leads, filter, id, dateRangeStart, dateRangeEnd) => {
        let filtered = leads;

        if (filter) {
            filtered = filtered.filter(lead => lead.retour_LF === filter);
        }

        if (id) {
            filtered = filtered.filter(lead => lead.id === id);
        }

        if (dateRangeStart && dateRangeEnd) {
            filtered = filtered.filter(lead => {
                const leadDate = new Date(lead.date_transmission);
                const startDate = new Date(dateRangeStart);
                const endDate = new Date(dateRangeEnd);
                return leadDate >= startDate && leadDate <= endDate;
            });
        }

        return filtered;
    };

    

    const handleButtonClick = async (leadId, leadInfo) => {
        const extendedLeadInfo = {
            ...leadInfo, // Copie les informations existantes
            source: '17', // Ajout de la source
        };

        console.log('Extended lead info:', extendedLeadInfo);
        try {
            // Mettre à jour le lead dans la base de données
            const updateResponse = await fetch(listURL.updateLead + leadId, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ retour_LF: 'OK' }),
            });

            if (!updateResponse.ok) {
                throw new Error(`Failed to update lead: ${updateResponse.status}`);
            }

            // Envoyer les informations du lead à l'URL externe
            const insertLead = await fetch(listURL.pushLead + leadId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(extendedLeadInfo),
            });

            if (!updateResponse.ok) {
                throw new Error(`Failed to update lead: ${updateResponse.status}`);
            }

            // Rafraîchir la page après les mises à jour réussies
            window.location.reload();
        } catch (error) {
            alert(`Erreur : ${error.message}`);
        }
    };



    return (
        <div className="container mt-5">
            <h1 className="mb-4">Liste des Leads</h1>
            {error && <p style={{ color: 'red' }}>Erreur : {error}</p>}

            <div className="mb-3 d-flex justify-content-between flex-wrap">
                <div className="me-2">
                    <label htmlFor="filterSelect" className="form-label">Filtrer par statut :</label>
                    <select 
                        id="filterSelect" 
                        className="form-select" 
                        value={filter} 
                        onChange={(e) => setFilter(e.target.value)} 
                    >
                        <option value="">Tous</option>
                        <option value="NOK">NOK</option>
                        <option value="OK">OK</option>
                        <option value="DBL">DBL</option>
                    </select>
                </div>

                <div className="me-2">
                    <label htmlFor="idLeadflow" className="form-label">Filtrer par ID de Lead :</label>
                    <input 
                        type="text" 
                        id="idLeadflow" 
                        className="form-control" 
                        value={id} 
                        onChange={(e) => setId(e.target.value)} 
                    />
                </div>

                <div className="me-2">
                    <label htmlFor="dateRangeStart" className="form-label">Date de début :</label>
                    <input 
                        type="datetime-local" 
                        id="dateRangeStart" 
                        className="form-control" 
                        value={dateRangeStart}
                        onChange={(e) => setDateRangeStart(e.target.value)} 
                    />
                </div>

                <div className="me-2">
                    <label htmlFor="dateRangeEnd" className="form-label">Date de fin :</label>
                    <input 
                        type="datetime-local" 
                        id="dateRangeEnd" 
                        className="form-control" 
                        value={dateRangeEnd}
                        onChange={(e) => setDateRangeEnd(e.target.value)} 
                    />
                </div>

                <div className="me-2 text-primary">
                    <input 
                        type="submit" 
                        id="dateRangeEnd" 
                        placeholder='Valider'
                        value='Valider'
                        className="form-control" 
                        onClick={() => setFilteredLeads(filterLeads(leads, filter, id, dateRangeStart, dateRangeEnd))}
                    />
                </div>
            </div>

            {filteredLeads.length === 0 ? (
                <p>Aucun lead trouvé.</p>
            ) : (
                <table className="table table-striped">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Date Transmission</th>
                            <th>Retour LF</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {filteredLeads.map((lead, index) => (
                            <tr key={index}>
                                <td>{lead.nom}</td>
                                <td>{lead.prenom}</td>
                                <td>{lead.email}</td>
                                <td>{lead.telephone}</td>
                                <td>{lead.date_transmission}</td>
                                <td>{lead.retour_LF}</td>
                                <td>
                                    {lead.retour_LF === 'NOK' && (
                                        <Button
                                            leadId={lead.id_leadflow}
                                            onClick={() => handleButtonClick(lead.id_leadflow, lead)}
                                        />
                                    )}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            )}
        </div>
    );
};

export default LeadList;
