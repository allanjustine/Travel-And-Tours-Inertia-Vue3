<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Sidebar from '@/Layouts/Sidebar.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import moment from 'moment';
import { watch } from 'vue';
import { ref, onMounted, computed } from 'vue';
import { Calendar, DatePicker } from 'v-calendar';
import 'v-calendar/style.css';


let props = defineProps({
    appointments: Array,
    user: Object,
    filters:Object,
    totalAppointments:Number,
    acceptedAppointment:Number,
    percentageChange:Number,
    cancelledAppointmentsByMonth: Number,
    upcomingAppointmentsByMonth: Number,
    totalUsers: Number,
})

    function formattedDate(date){
        return moment(date).format('MMMM   D, YYYY');
    }

    const formatTimeToAMPM = (time) => {
        const [hours, minutes] = time.split(':').map(Number);
        const period = hours >= 12 ? 'PM' : 'AM';
        const formattedHours = hours % 12 || 12;
        const formattedMinutes = minutes.toString().padStart(2, '0');
        return `${formattedHours}:${formattedMinutes} ${period}`;
    };

    const currentTime = ref('');

    onMounted(() => {
    // Get the current time
    const now = new Date();
    const hours = now.getHours();
    const minutes = now.getMinutes();
    currentTime.value = `${hours}:${minutes < 10 ? '0' : ''}${minutes}`;
    });

    const greeting = computed(() => {
    const hour = currentTime.value ? parseInt(currentTime.value.split(':')[0]) : 0;

    if (hour >= 5 && hour < 12) {
        return 'Good Morning';
    } else if (hour >= 12 && hour < 17) {
        return 'Good Afternoon';
    } else {
        return 'Good Evening';
    }
    });

    let search = ref(props.filters.search);
    watch(search, (value) => {
        router.get(
            "/dashboard",
            { search: value },
            {
                preserveState: true,
                replace: true,
            }
        );
    });

const attrs = ref([
  ...props.appointments.map(appointment => {
    const appointmentDate = new Date(appointment.date);
    console.log('Raw appointment time:', appointment.time);

    const appointmentTime = appointment.time.split(':');
    const formattedTime = new Date(appointmentDate.getFullYear(), appointmentDate.getMonth(), appointmentDate.getDate(), parseInt(appointmentTime[0], 10), parseInt(appointmentTime[1], 10)).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    console.log('Popover label:', appointment.popoverLabel);

    let highlightColor = '';
    switch (appointment.status) {
      case 'Pending':
        highlightColor = 'blue';
        break;
      case 'Accepted':
        highlightColor = 'green';
        break;
      case 'Cancelled':
        highlightColor = 'red';
        break;
      default:
        highlightColor = 'gray'; // You can set a default color for other statuses
    }


    return {
    key: `appointment-${appointment.id}`,
      highlight: highlightColor,
      style: 'background-color: red; color: white;',
      dates: appointmentDate,
      popover: {
        // label: `${appointment.patient.firstname} ${appointment.patient.lastname}\n${formattedTime}`, // Display patient's name and formatted time in the popover
        label: appointment.popoverLabel || '',
        visibility: 'hover', // Show popover on hover
      },
    };
  }),
]);



    const formattedAppointments = computed(() => {
      return props.appointments.map(appointment => {
        return {
          start: (appointment.date, appointment.time),
          title: `${appointment.patient.firstname} - ${appointment.doctor}`,
          // Add other properties as needed based on your appointment structure
        };
      });
    });

</script>

<template>
    <Head title="Dashboard" />

    <Sidebar>
        <div class="flex flex-col flex-1 w-full overflow-y-auto">

          <main class="relative z-0 flex-1 pb-8 px-6 ">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">


            <div class="bg-white p-6 rounded-md shadow-md flex items-center">
                <div class="mr-4">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 3h6m-3 0l-9 9m9-9V6a9 9 0 00-9 9c0 4.418 3.112 8 7 8s7-3.582 7-8a1 1 0 00-2 0c0 3.314-2.239 6-5 6s-5-2.686-5-6v-1m9-9l-3 3"></path></svg>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-700">Total Appointments</p>
                    <p class="text-3xl font-bold text-red-500">{{ totalAppointments }}</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-md shadow-md flex items-center">
                <div class="mr-4">
                    <i class="w-8 h-8 text-violet-500 fas fa-users"></i>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-700">Total Users</p>
                    <p class="text-3xl font-bold text-violet-500">{{ totalUsers }}</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-md shadow-md flex items-center">
                <div class="mr-4">

                    <i class="w-8 h-8 text-blue-500 fas fa-clock"></i>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-700">Pending Appointments</p>
                    <p class="text-3xl font-bold text-blue-500">{{ upcomingAppointmentsByMonth }}</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-md shadow-md flex items-center">
                <div class="mr-4">

                    <i class="w-8 h-8 text-green-500 fas fa-check-double"></i>
                </div>
                <div>
                    <p class="text-lg font-semibold text-green-700">Accepted Appointments</p>
                    <p class="text-3xl font-bold text-green-500">{{ acceptedAppointment }}</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-md shadow-md flex items-center">
                <div class="mr-4">

                    <i class="w-8 h-8 text-red-500 fas fa-ban"></i>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-700">Cancelled Appointments</p>
                    <p class="text-3xl font-bold text-red-500">{{cancelledAppointmentsByMonth}}</p>
                </div>
            </div>

        </div>
              <div class="pt-8 px-2" >
                <div class="w-full grid grid-cols-2 xl:grid-cols-3 2xl:grid-cols-3 gap-4">
                   <div class="bg-white shadow rounded-lg p-4 sm:p-6 xl:p-8 xl:col-span-2 2xl:col-span-2">
                        <div class="flex justify-between ">
                            <h3 class="flex items-start justify-center m-2 ml-0 font-medium text-xl/tight text-dark">
                                <span class="mr-3 text-dark font-bold">Recent Appointments</span>
                            </h3>
                            <Link :href="route('appointment.index')" class=" inline-block text-[.925rem] font-medium leading-normal text-right align-right cursor-pointer rounded-2xl transition-colors duration-150 ease-in-out text-blue-400 bg-light-dark border-light shadow-none border-0 py-2 px-5 sm:self-center">
                                See More
                            </Link>
                        </div>
                        <div class="flex flex-col">
                            <div class="-m-1.5 overflow-x-auto">
                                <div class="p-1.5 min-w-full inline-block align-middle">
                                <div class=" rounded-lg divide-y divide-gray-200 ">
                                    <!-- <div class="py-3 px-4">
                                        <div class="relative max-w-xs">
                                            <label for="hs-table-search" class="sr-only">Search</label>
                                            <input type="search"  v-model="search" name="hs-table-search" id="hs-table-search" class="py-2 px-3 ps-9 block w-full border-gray-200 shadow-sm rounded-lg text-sm focus:z-10 focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none " placeholder="Search for appointment">
                                            <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-3">
                                            <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                            </div>
                                        </div>
                                    </div> -->
                                    <div class="overflow-hidden">
                                    <table class="min-w-full divide-y divide-gray-200 ">
                                        <thead class="bg-gray-50 ">
                                            <tr>

                                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">Tourist</th>
                                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">Date and Time</th>
                                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">Hotel</th>
                                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">Status</th>
                                            <!-- <th scope="col" class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase">Action</th> -->
                                            </tr>
                                        </thead>
                                            <tbody class="divide-y divide-gray-200">

                                                <tr v-for="app in appointments" :key="app.id">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 ">{{ app.user.name }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 ">{{ formattedDate(app.date) }} at {{ formatTimeToAMPM(app.time) }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 ">{{ app.post.hotel_name }}</td>
                                                    <td class=" text-xs whitespace-nowrap  uppercase px-6 py-4  text-center font-bold " :class="{
                                                                        'text-blue-600': app.status == 'Pending',
                                                                        'text-red-600':app.status == 'Cancelled',
                                                                        'text-green-600': app.status =='Accepted',
                                                                    }"><i class="fas fa-check-double" v-if="app.status =='Accepted'"></i> {{ app.status}}</td>
                                                    <!-- <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                                                    <button type="button" class="inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent text-blue-600 hover:text-blue-800 disabled:opacity-50 disabled:pointer-events-none dark:text-blue-500 dark:hover:text-blue-400 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600">Delete</button>
                                                    </td> -->
                                                </tr>

                                                <tr v-if="appointments == 0">
                                                <td colspan="4" class="text-center">
                                                    No records found
                                                </td>
                                                </tr>
                                             </tbody>
                                        </table>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>

                    </div>

                        <Calendar :attributes="attrs" class="my-custom-calendar py-10 " expanded />


                    </div>


            </div>
            <!-- <div class="pt-8 px-2" >
                <div class="w-full grid grid-cols-1 xl:grid-cols-1 2xl:grid-cols-3 gap-4">
                   <div class="bg-white shadow rounded-lg p-4 sm:p-6 xl:p-8 2xl:col-span-2">

                   </div>
                    <div class="bg-white shadow rounded-lg p-4 sm:p-6 xl:p-8 2xl:col-span-1">

                    </div>
                </div>
            </div> -->
          </main>

        </div>


    </Sidebar>
</template>
<style scoped>
.my-custom-calendar {
    width: 100% ;
    height: 100%;

}

/* #app {
  background: #fff;
  border-radius: 4px;
  padding: 20px;
  transition: all 0.2s;
  text-align: center;
} */
</style>
