import React, { useState } from 'react';
import { 
  Users, UserPlus, FileText, ClipboardList, 
  Pill, CreditCard, BarChart3, Activity, 
  Settings, LogOut, Search, Plus, CheckCircle2, 
  Clock, Printer, AlertCircle, Stethoscope, Syringe
} from 'lucide-react';

// --- MOCK DATA ---
const mockQueue = [
  { id: 'A001', name: 'Budi Santoso', status: 'Diperiksa', dokter: 'dr. Andi (Umum)' },
  { id: 'A002', name: 'Siti Aminah', status: 'Menunggu', dokter: 'dr. Andi (Umum)' },
  { id: 'A003', name: 'Rudi Hermawan', status: 'Menunggu', dokter: 'Bidan Nita (KIA)' },
];

const mockPrescriptions = [
  { id: 'RSP-001', patient: 'Budi Santoso', doctor: 'dr. Andi', status: 'Menunggu Racikan', items: ['Paracetamol 500mg (10)', 'Amoxicillin (10)'] },
  { id: 'RSP-002', patient: 'Cici Paramida', doctor: 'dr. Budi', status: 'Selesai', items: ['Vitamin C (30)'] },
];

const mockBilling = [
  { id: 'INV-101', patient: 'Cici Paramida', status: 'Belum Bayar', total: 150000, details: 'Pemeriksaan Umum, Obat Vitamin C' },
];

const App = () => {
  const [role, setRole] = useState('Resepsionis'); // Default role
  const [activeTab, setActiveTab] = useState('dashboard');

  // --- LAYOUT COMPONENTS ---
  const Sidebar = () => {
    let menuItems = [];
    
    if (role === 'Pasien') {
      menuItems = [
        { id: 'dashboard', label: 'Layar Antrian', icon: <Activity size={20} /> },
        { id: 'daftar', label: 'Pendaftaran Mandiri', icon: <UserPlus size={20} /> },
      ];
    } else if (role === 'Resepsionis') {
      menuItems = [
        { id: 'dashboard', label: 'Dashboard', icon: <Activity size={20} /> },
        { id: 'pendaftaran', label: 'Pendaftaran & Antrian', icon: <Users size={20} /> },
        { id: 'kasir', label: 'Kasir & Pembayaran', icon: <CreditCard size={20} /> },
        { id: 'laporan', label: 'Laporan Klinik', icon: <BarChart3 size={20} /> },
      ];
    } else if (role === 'Dokter') {
      menuItems = [
        { id: 'dashboard', label: 'Dashboard Medis', icon: <Activity size={20} /> },
        { id: 'antrian', label: 'Antrian Pasien', icon: <Users size={20} /> },
        { id: 'emr', label: 'Rekam Medis (EMR)', icon: <ClipboardList size={20} /> },
      ];
    } else if (role === 'Apoteker') {
      menuItems = [
        { id: 'dashboard', label: 'Dashboard Farmasi', icon: <Activity size={20} /> },
        { id: 'resep', label: 'Antrian Resep', icon: <FileText size={20} /> },
        { id: 'stok', label: 'Stok Obat', icon: <Pill size={20} /> },
      ];
    }

    return (
      <div className="w-64 bg-slate-900 text-white flex flex-col min-h-screen transition-all duration-300">
        <div className="p-6 flex items-center gap-3 border-b border-slate-700/50">
          <div className="bg-blue-500 p-2 rounded-lg text-white">
            <Stethoscope size={24} />
          </div>
          <div>
            <h1 className="font-bold text-lg leading-tight">Klinik Sehat</h1>
            <p className="text-xs text-slate-400">Management System</p>
          </div>
        </div>
        
        <div className="flex-1 py-6 px-4 space-y-2">
          <div className="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-4 px-2">Menu Utama</div>
          {menuItems.map((item) => (
            <button
              key={item.id}
              onClick={() => setActiveTab(item.id)}
              className={`w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all ${
                activeTab === item.id 
                  ? 'bg-blue-600 text-white shadow-md shadow-blue-900/20' 
                  : 'text-slate-300 hover:bg-slate-800 hover:text-white'
              }`}
            >
              {item.icon}
              <span className="font-medium text-sm">{item.label}</span>
            </button>
          ))}
        </div>
      </div>
    );
  };

  const Header = () => (
    <header className="bg-white h-20 border-b border-slate-200 flex items-center justify-between px-8 sticky top-0 z-10 shadow-sm">
      <div className="flex items-center gap-4">
        <h2 className="text-xl font-bold text-slate-800 capitalize">
          {activeTab.replace('-', ' ')}
        </h2>
      </div>
      
      <div className="flex items-center gap-6">
        {/* Role Switcher untuk keperluan Wireframe */}
        <div className="flex items-center gap-3 bg-slate-100 p-2 rounded-lg border border-slate-200">
          <span className="text-sm text-slate-500 font-medium">Lihat sebagai:</span>
          <select 
            value={role}
            onChange={(e) => {
              setRole(e.target.value);
              setActiveTab('dashboard'); // Reset tab on role change
            }}
            className="text-sm bg-white border border-slate-300 rounded px-3 py-1 font-semibold text-blue-700 outline-none focus:border-blue-500 cursor-pointer"
          >
            <option value="Pasien">Pasien</option>
            <option value="Resepsionis">Resepsionis</option>
            <option value="Dokter">Dokter / Bidan</option>
            <option value="Apoteker">Apoteker</option>
          </select>
        </div>

        <div className="w-px h-8 bg-slate-200"></div>

        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold border border-blue-200">
            {role.charAt(0)}
          </div>
          <div className="hidden md:block">
            <p className="text-sm font-bold text-slate-700 leading-tight">Nama User</p>
            <p className="text-xs text-slate-500">{role}</p>
          </div>
        </div>
      </div>
    </header>
  );

  // --- VIEWS / PAGES ---

  // 1. PASIEN VIEWS
  const ViewPasienAntrian = () => (
    <div className="space-y-6">
      <div className="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-8 text-white shadow-lg text-center">
        <h3 className="text-lg text-blue-100 mb-2">Nomor Antrian Anda</h3>
        <div className="text-6xl font-black mb-4 tracking-wider">A002</div>
        <div className="inline-flex items-center gap-2 bg-white/20 px-4 py-2 rounded-full text-sm backdrop-blur-sm">
          <Clock size={16} /> Status: Menunggu Panggilan
        </div>
      </div>
      
      <div className="grid grid-cols-2 gap-6">
        <div className="bg-white p-6 rounded-xl border border-slate-200 shadow-sm text-center">
          <h4 className="text-slate-500 font-medium mb-2">Antrian Saat Ini (Poli Umum)</h4>
          <div className="text-4xl font-bold text-slate-800">A001</div>
          <p className="text-sm text-slate-500 mt-2">Menuju Ruang Periksa 1</p>
        </div>
        <div className="bg-white p-6 rounded-xl border border-slate-200 shadow-sm text-center">
          <h4 className="text-slate-500 font-medium mb-2">Antrian Saat Ini (Poli KIA)</h4>
          <div className="text-4xl font-bold text-slate-800">B005</div>
          <p className="text-sm text-slate-500 mt-2">Menuju Ruang Periksa 2</p>
        </div>
      </div>
    </div>
  );

  // 2. RESEPSIONIS VIEWS
  const ViewResepsionisPendaftaran = () => (
    <div className="grid grid-cols-3 gap-6">
      <div className="col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col h-[600px]">
        <div className="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
          <h3 className="font-bold text-slate-800">Daftar Antrian Hari Ini</h3>
          <div className="relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" size={16} />
            <input type="text" placeholder="Cari nama pasien..." className="pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm w-64 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
          </div>
        </div>
        <div className="flex-1 overflow-auto p-4">
          <table className="w-full text-left text-sm">
            <thead className="bg-slate-50 text-slate-500 sticky top-0">
              <tr>
                <th className="p-3 font-semibold rounded-tl-lg">No. Antrian</th>
                <th className="p-3 font-semibold">Nama Pasien</th>
                <th className="p-3 font-semibold">Dokter Tujuan</th>
                <th className="p-3 font-semibold">Status</th>
                <th className="p-3 font-semibold rounded-tr-lg">Aksi</th>
              </tr>
            </thead>
            <tbody>
              {mockQueue.map((q, i) => (
                <tr key={i} className="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                  <td className="p-3 font-bold text-slate-700">{q.id}</td>
                  <td className="p-3">{q.name}</td>
                  <td className="p-3 text-slate-600">{q.dokter}</td>
                  <td className="p-3">
                    <span className={`px-2.5 py-1 rounded-full text-xs font-medium ${
                      q.status === 'Menunggu' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700'
                    }`}>
                      {q.status}
                    </span>
                  </td>
                  <td className="p-3">
                    <button className="text-blue-600 hover:text-blue-800 font-medium text-xs bg-blue-50 px-3 py-1.5 rounded-lg transition-colors">Edit</button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
      
      <div className="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h3 className="font-bold text-slate-800 flex items-center gap-2 mb-6">
          <Plus size={18} className="text-blue-600" />
          Registrasi Pasien Baru
        </h3>
        <form className="space-y-4">
          <div>
            <label className="block text-xs font-semibold text-slate-500 mb-1">Nama Lengkap</label>
            <input type="text" className="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="Masukkan nama..." />
          </div>
          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className="block text-xs font-semibold text-slate-500 mb-1">Tgl Lahir</label>
              <input type="date" className="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
            </div>
            <div>
              <label className="block text-xs font-semibold text-slate-500 mb-1">Jenis Kelamin</label>
              <select className="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                <option>Laki-laki</option>
                <option>Perempuan</option>
              </select>
            </div>
          </div>
          <div>
            <label className="block text-xs font-semibold text-slate-500 mb-1">Poliklinik Tujuan</label>
            <select className="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
              <option>Poli Umum - dr. Andi</option>
              <option>Poli KIA - Bidan Nita</option>
            </select>
          </div>
          <button type="button" className="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg text-sm mt-4 transition-colors shadow-sm shadow-blue-200">
            Cetak Nomor Antrian
          </button>
        </form>
      </div>
    </div>
  );

  const ViewResepsionisKasir = () => (
    <div className="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div className="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
        <h3 className="font-bold text-slate-800 text-lg">Menunggu Pembayaran</h3>
      </div>
      <div className="p-6">
        <table className="w-full text-left">
          <thead className="text-slate-500 text-sm border-b border-slate-200">
            <tr>
              <th className="pb-3 font-semibold">No. Invoice</th>
              <th className="pb-3 font-semibold">Nama Pasien</th>
              <th className="pb-3 font-semibold">Rincian</th>
              <th className="pb-3 font-semibold text-right">Total Biaya</th>
              <th className="pb-3 font-semibold text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            {mockBilling.map((bill, i) => (
              <tr key={i} className="border-b border-slate-100">
                <td className="py-4 font-bold text-slate-700">{bill.id}</td>
                <td className="py-4 font-medium">{bill.patient}</td>
                <td className="py-4 text-sm text-slate-500">{bill.details}</td>
                <td className="py-4 font-bold text-right text-emerald-600">Rp {bill.total.toLocaleString('id-ID')}</td>
                <td className="py-4 text-center">
                  <button className="bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white border border-emerald-200 hover:border-emerald-600 px-4 py-2 rounded-lg text-sm font-semibold transition-colors inline-flex items-center gap-2">
                    <Printer size={16} /> Cetak Struk
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );

  // 3. DOKTER VIEWS
  const ViewDokterEMR = () => (
    <div className="space-y-6">
      {/* Patient Header */}
      <div className="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div className="flex items-center gap-4">
          <div className="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center text-slate-500 border border-slate-200">
            <Users size={24} />
          </div>
          <div>
            <h3 className="text-xl font-bold text-slate-800">Budi Santoso</h3>
            <div className="text-sm text-slate-500 flex gap-4 mt-1">
              <span>No RM: <strong>RM-2023-0899</strong></span>
              <span>•</span>
              <span>Laki-laki, 34 Tahun</span>
              <span>•</span>
              <span>Antrian: <strong className="text-blue-600">A001</strong></span>
            </div>
          </div>
        </div>
        <button className="text-blue-600 font-medium text-sm border border-blue-200 bg-blue-50 px-4 py-2 rounded-lg hover:bg-blue-100 transition-colors">
          Lihat Riwayat Kunjungan
        </button>
      </div>

      <div className="grid grid-cols-2 gap-6">
        {/* Pemeriksaan Form */}
        <div className="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-5">
          <h4 className="font-bold text-slate-800 flex items-center gap-2 border-b border-slate-100 pb-3">
            <ClipboardList size={18} className="text-blue-600" /> Hasil Pemeriksaan (SOAP)
          </h4>
          
          <div className="grid grid-cols-2 gap-4">
             <div>
              <label className="block text-xs font-semibold text-slate-500 mb-1">Keluhan (Subjective)</label>
              <textarea className="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500" rows="2" placeholder="Demam 3 hari..."></textarea>
            </div>
             <div>
              <label className="block text-xs font-semibold text-slate-500 mb-1">Fisik (Objective)</label>
              <textarea className="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500" rows="2" placeholder="Tensi, Suhu..."></textarea>
            </div>
          </div>
          
          <div>
            <label className="block text-xs font-semibold text-slate-500 mb-1">Diagnosis (Assessment)</label>
            <input type="text" className="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500" placeholder="Kode ICD-10 / Nama Penyakit..." />
          </div>

          <div>
             <label className="block text-xs font-semibold text-slate-500 mb-1">Tindakan (Plan)</label>
            <textarea className="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500" rows="2" placeholder="Edukasi pasien, dsb..."></textarea>
          </div>
        </div>

        {/* E-Resep Form */}
        <div className="bg-white rounded-xl border border-slate-200 shadow-sm flex flex-col h-full">
          <div className="p-6 border-b border-slate-100 flex justify-between items-center">
            <h4 className="font-bold text-slate-800 flex items-center gap-2">
              <Pill size={18} className="text-emerald-500" /> Resep Obat (E-Resep)
            </h4>
          </div>
          <div className="p-6 flex-1 space-y-4">
             <div className="flex gap-2">
                <input type="text" className="flex-1 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-emerald-500" placeholder="Cari obat (Misal: Paracetamol)..." />
                <button className="bg-slate-100 text-slate-600 px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-200">
                  <Plus size={18} />
                </button>
             </div>
             
             {/* Daftar Obat Ditambahkan */}
             <div className="border border-slate-200 rounded-lg bg-slate-50 min-h-[120px] p-3 space-y-2">
                <div className="bg-white border border-slate-200 p-2 rounded-md text-sm flex justify-between items-center">
                   <div>
                     <span className="font-semibold text-slate-700">Paracetamol 500mg</span>
                     <p className="text-xs text-slate-500">Jumlah: 10 tablet | Dosis: 3 x 1 sesudah makan</p>
                   </div>
                   <button className="text-red-500 hover:text-red-700 p-1">Hapus</button>
                </div>
                <div className="bg-white border border-slate-200 p-2 rounded-md text-sm flex justify-between items-center">
                   <div>
                     <span className="font-semibold text-slate-700">Amoxicillin 500mg</span>
                     <p className="text-xs text-slate-500">Jumlah: 15 tablet | Dosis: 3 x 1 dihabiskan</p>
                   </div>
                   <button className="text-red-500 hover:text-red-700 p-1">Hapus</button>
                </div>
             </div>
          </div>
          
          <div className="p-4 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-3 rounded-b-xl">
             <button className="px-4 py-2 text-sm font-medium text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-100">
               Selesai Tanpa Obat
             </button>
             <button className="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm">
               Simpan RM & Kirim Resep
             </button>
          </div>
        </div>
      </div>
    </div>
  );

  // 4. APOTEKER VIEWS
  const ViewApotekerResep = () => (
    <div className="bg-white rounded-xl border border-slate-200 shadow-sm">
      <div className="p-6 border-b border-slate-100">
        <h3 className="font-bold text-slate-800">Antrian Resep Obat</h3>
      </div>
      <div className="p-0">
        <table className="w-full text-left">
          <thead className="bg-slate-50 text-slate-500 text-sm">
            <tr>
              <th className="p-4 font-semibold">ID Resep</th>
              <th className="p-4 font-semibold">Pasien</th>
              <th className="p-4 font-semibold">Dokter</th>
              <th className="p-4 font-semibold">Detail Obat</th>
              <th className="p-4 font-semibold text-center">Status / Aksi</th>
            </tr>
          </thead>
          <tbody>
            {mockPrescriptions.map((resep, i) => (
              <tr key={i} className="border-b border-slate-100 align-top">
                <td className="p-4 font-bold text-slate-700 text-sm">{resep.id}</td>
                <td className="p-4 text-sm">{resep.patient}</td>
                <td className="p-4 text-sm text-slate-600">{resep.doctor}</td>
                <td className="p-4">
                  <ul className="list-disc pl-4 text-sm text-slate-700 space-y-1">
                    {resep.items.map((item, idx) => <li key={idx}>{item}</li>)}
                  </ul>
                </td>
                <td className="p-4 text-center">
                  {resep.status === 'Menunggu Racikan' ? (
                    <button className="bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-600 hover:text-white px-3 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2 mx-auto">
                      <CheckCircle2 size={16} /> Konfirmasi Selesai
                    </button>
                  ) : (
                    <span className="inline-flex items-center gap-1 text-emerald-600 font-semibold text-sm bg-emerald-50 px-3 py-1.5 rounded-full">
                      <CheckCircle2 size={14} /> Selesai
                    </span>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );

  // --- MAIN RENDER LOGIC ---
  const renderContent = () => {
    // Default placeholder for unbuilt tabs
    const Placeholder = () => (
      <div className="h-64 flex flex-col items-center justify-center text-slate-400 border-2 border-dashed border-slate-200 rounded-xl bg-slate-50">
        <Activity size={48} className="mb-4 opacity-50" />
        <p className="font-medium">Modul "{activeTab}" sedang dalam pengembangan</p>
      </div>
    );

    switch (role) {
      case 'Pasien':
        if (activeTab === 'dashboard') return <ViewPasienAntrian />;
        break;
      case 'Resepsionis':
        if (activeTab === 'pendaftaran') return <ViewResepsionisPendaftaran />;
        if (activeTab === 'kasir') return <ViewResepsionisKasir />;
        break;
      case 'Dokter':
        if (activeTab === 'emr') return <ViewDokterEMR />;
        break;
      case 'Apoteker':
        if (activeTab === 'resep') return <ViewApotekerResep />;
        break;
      default:
        return <Placeholder />;
    }

    // Default return if tab matches but no specific component built for wireframe
    return (
      <div className="grid grid-cols-3 gap-6 mb-6">
        <div className="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
          <div className="p-4 bg-blue-50 text-blue-600 rounded-lg"><Users size={24} /></div>
          <div><p className="text-slate-500 text-sm font-medium">Total Pasien Hari Ini</p><h4 className="text-2xl font-bold text-slate-800">42</h4></div>
        </div>
        <div className="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
          <div className="p-4 bg-emerald-50 text-emerald-600 rounded-lg"><CheckCircle2 size={24} /></div>
          <div><p className="text-slate-500 text-sm font-medium">Selesai Diperiksa</p><h4 className="text-2xl font-bold text-slate-800">28</h4></div>
        </div>
        <div className="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
          <div className="p-4 bg-amber-50 text-amber-600 rounded-lg"><Clock size={24} /></div>
          <div><p className="text-slate-500 text-sm font-medium">Menunggu Antrian</p><h4 className="text-2xl font-bold text-slate-800">14</h4></div>
        </div>
      </div>
    );
  };

  return (
    <div className="flex min-h-screen bg-slate-50/50 font-sans">
      <Sidebar />
      <main className="flex-1 flex flex-col">
        <Header />
        <div className="flex-1 p-8 overflow-y-auto">
          {renderContent()}
        </div>
      </main>
    </div>
  );
};

export default App;