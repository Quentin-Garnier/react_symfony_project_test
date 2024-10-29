// App.js
import './App.css';
import LeadList from './components/LeadList';
import React from 'react';
import ConnectionForm from './components/users/ConnectonForm';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Header from './components/Header';
import InscriptionForm from './components/users/Inscription';
import GetTasks from './components/tasks/GetTasks';

function App() {
  return (
    <div className="App">
      <Router>
        <Header />
        <Routes>
          <Route path="/" element={<LeadList />} />
          <Route path="/connection" element={<ConnectionForm />} />
          <Route path="/inscription" element={<InscriptionForm />} />
          <Route path="/tasks" element={<GetTasks />} />
          <Route path='*' element={<div>404</div>} />
        </Routes>
      </Router>
    </div>
  );
}

export default App;
