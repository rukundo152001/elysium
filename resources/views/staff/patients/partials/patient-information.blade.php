<!-- resources/views/components/patient-information.blade.php -->
<div 
  x-data="patientInformationComponent()" 
  x-init="init()" 
  class="bg-gradient-to-br from-teal-50 to-white border border-teal-200 rounded-2xl p-6 shadow-sm mb-6"
>
  <h2 class="text-2xl font-bold text-teal-900 mb-6 flex items-center">
    <span class="bg-gradient-to-br from-teal-500 to-teal-600 text-white w-10 h-10 rounded-full flex items-center justify-center mr-3 text-lg font-semibold shadow-md">1</span>
    Patient Information
  </h2>

  
    

  <div class="grid grid-cols-1 md:grid-cols-2 gap-5">


    
    
    <!-- ref_number -->
    <div>
      <label class="block text-sm font-semibold text-teal-900 mb-2">Reference Number</label>
      <input 
        x-model="patient.ref_number"
        type="text" 
        placeholder="Auto or manual ref"
        class="w-full border-2 rounded-xl px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
      >
    </div>

    <!-- referred_by -->
    <div>
      <label class="block text-sm font-semibold text-teal-900 mb-2">Referred Hospital</label>
      <input 
        x-model="patient.referred_from"
        type="text"
        placeholder="Patient Hosp from:"
        class="w-full border-2 rounded-xl px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
      >
    </div>
    <!-- Full Name -->
    <div>
      <label class="block text-sm font-semibold text-teal-900 mb-2">Full Name</label>
      <input 
        x-model="patient.name"
        type="text"
        placeholder="John Doe"
        class="w-full border-2 rounded-xl px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
      >
    </div>

    <!-- Gender -->
    <div>
      <label class="block text-sm font-semibold text-teal-900 mb-2">Gender</label>
      <select 
        x-model="patient.gender"
        class="w-full border-2 rounded-xl px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
      >
        <option value="">Select Gender</option>
        <option value="male">Male</option>
        <option value="female">Female</option>
      </select>
    </div>

    <!-- Date of Birth -->
    <div>
      <label class="block text-sm font-semibold text-teal-900 mb-2">Date of Birth</label>
      <input 
        x-model="patient.dob"
        :max="dobMaxDate"
        type="date"
        class="w-full border-2 rounded-xl px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
      >
    </div>

    <!-- Phone Number -->
    <div>
      <label class="block text-sm font-semibold text-teal-900 mb-2">Phone Number</label>
      <input 
        x-model="patient.phone"
        type="tel"
        placeholder="+250 7XX XXX XXX"
        class="w-full border-2 rounded-xl px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
      >
    </div>


    
    <div class="border-t border-black">
      <label class="block text-sm font-semibold text-teal-900 mb-2">Technician</label>
      <select 
        x-model="patient.technician_id" 
        class="w-full border-2 rounded-xl px-4 py-2.5 bg-gray-50 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
      >
        <option value="">Select Technician</option>
        @foreach ($technicians as $tech)
          <option value="{{ $tech->id }}">{{ $tech->name }}</option>
        @endforeach
      </select>
    </div>
    

    <div class="border-t border-black">
  <label class="block text-sm font-semibold text-teal-900 mb-2">Treatment Date</label>
  <input 
    x-model="patient.treatment_date"
    :max="maxDate"
    min="1900-01-01"
    type="date"
    class="w-full border-2 rounded-xl px-4 py-2.5 bg-gray-50 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
  >
</div>

  </div>

  <!-- Added hidden input to store all patient data as JSON -->
  <input type="hidden" name="patient_data" x-model="patientDataJSON">
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('patientInformationComponent', () => ({
    patient: {
      ref_number: '',
      referred_from:'',
      name: '',
      gender: '',
      dob: '',
      phone: '',
      technician_id: '',
      treatment_date: '',
    },
    patientDataJSON: '',
    maxDate: '',      // for treatment_date
    dobMaxDate: '',   // for date of birth

    init() {
      // Register globally
      if (window.patientRegistrationStore) {
        window.patientRegistrationStore.patientComponent = this;
        console.log('[v0] Patient component registered in global store');
      }

      // Prefill if editing
      if (window.currentPatient) {
        this.patient = window.currentPatient;
      }

      // ✅ Initialize today's date limits (local time)
      this.setToday();

      // ✅ Start periodic local date refresh
      this.startDailyCheck();

      // ✅ Auto-sync JSON
      this.updatePatientDataJSON();
      this.$watch('patient', () => this.updatePatientDataJSON(), { deep: true });

      // ✅ Prevent future treatment date
      this.$watch('patient.treatment_date', value => {
        const today = this.getLocalDate();
        if (value > today) {
          console.warn('[v0] Prevented future treatment date:', value);
          this.patient.treatment_date = today;
        }
      });

      // ✅ Prevent future DOB
      this.$watch('patient.dob', value => {
        const today = this.getLocalDate();
        if (value > today) {
          console.warn('[v0] Prevented future DOB:', value);
          this.patient.dob = today;
        }
      });
    },

    // ✅ Get local date as YYYY-MM-DD (not UTC)
    getLocalDate() {
      return new Date().toLocaleDateString('en-CA'); // always local format
    },

    // ✅ Set today's limits
    setToday() {
      const today = this.getLocalDate();
      this.maxDate = today;
      this.dobMaxDate = today;
      if (!this.patient.treatment_date) {
        this.patient.treatment_date = today;
      }
      console.log('[v0] Local date limits refreshed to:', today);
    },

    // ✅ Keep updating if day changes (every 1 minute)
    startDailyCheck() {
      this.setToday();
      setInterval(() => {
        const today = this.getLocalDate();
        if (this.maxDate !== today) {
          console.log('[v0] Auto-updated local date after midnight to:', today);
          this.setToday();
        }
      }, 60000); // check every minute
    },

    // ✅ Sync JSON data for backend
    updatePatientDataJSON() {
      this.patientDataJSON = JSON.stringify(this.patient);
    },

    getPatientData() {
      return this.patient;
    }
  }));
});
</script>
