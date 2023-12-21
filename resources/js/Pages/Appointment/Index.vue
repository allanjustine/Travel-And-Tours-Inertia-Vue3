<script setup>
import Sidebar from '@/Layouts/Sidebar.vue';
// import Pagination from '@/Components/Pagination.vue';
import { Link, router, useForm, Head } from '@inertiajs/vue3';
import { computed } from '@vue/reactivity';
import moment from 'moment';
import { watch } from 'vue';
import { ref, onMounted, watchEffect,reactive } from 'vue'
  import { inject } from 'vue';
const openTab = ref(1);
const sort = ref('latest');

const toggleDarkMode = inject('isDarkMode');
const props = defineProps({
        appointments: Array,
        isAdmin:Boolean,
        user:Object,
        acceptedApp: Array,
        cancelApp : Array,
        filters: Object,
        filter: Object
})

const filter = reactive({...props.filter})
    const isMounted = ref(false)

    const fetchAppointment = () => {
    if (isMounted.value) {
        router.visit(route('appointment.index', { from_date: filter.from_date, to_date: filter.to_date }), { preserveState: true });
    }
}
const resetFilters = () => {
    filter.from_date = '';
    filter.to_date = '';
    fetchAppointment();
}



onMounted(() => {
    isMounted.value = true;
    fetchAppointment();
})

watchEffect(() => {
    if (isMounted.value) {
        fetchAppointment()
    }
})
const isAdmin = props.isAdmin;

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

const sortedAppointments = computed(() => {
    let appointments = [...props.appointments];

    // Sort the appointments based on the selected sort order
    if (sort.value === 'latest') {
    appointments.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    } else if (sort.value === 'oldest') {
    appointments.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
    }

    return appointments;


});

// const openTab = ref(1);

watch(() => openTab.value, (newValue) => {
    console.log('openTab changed:', newValue);
});

let search = ref(props.filters.search);
    watch(search, (value) => {
        router.get(
            "/appointment/index",
            { search: value },
            {
                preserveState: true,
                replace: true,
            }
        );
    });
    onMounted(() => {
// Set a timeout to hide the success flash message after 3 seconds
const successFlashMessage = document.getElementById('flash-success-message');
if (successFlashMessage) {
setTimeout(() => {
    successFlashMessage.style.display = 'none';
}, 3000);
}

// Set a timeout to hide the error flash message after 3 seconds
const errorFlashMessage = document.getElementById('flash-error-message');
if (errorFlashMessage) {
setTimeout(() => {
    errorFlashMessage.style.display = 'none';
}, 3000);
}
});
</script>

<template>
    <Sidebar>
        <Head title="Appointment List"/>
        <div class="sm:px-6 w-full" :class="themeMode">

        <div class="px-4 md:px-10 py-4 md:py-7">
            <div class="flex items-center justify-between">
                <p tabindex="0" class="focus:outline-none text-base sm:text-lg md:text-xl lg:text-2xl font-bold leading-normal">Appointment Lists</p>



            </div>
             <div v-if="$page.props.flash.success" id="flash-success-message" class="absolute top-20 right-1 p-4 bg-green-300 border border-gray-300 rounded-md shadow-md">
                {{ $page.props.flash.success }}
                <div class="progress-bar success"></div>
            </div>
            <div v-if="$page.props.flash.error" id="flash-error-message" class=" absolute top-20 right-1 p-4 bg-red-300 border border-gray-300 rounded-md shadow-md">
                {{ $page.props.flash.error }}
                <div class="progress-bar error"></div>
            </div>
                <div class="inline-flex overflow-hidden float-end mt-10" :class="themeMode">
                    <div class="flex flex-col md:flex-row items-center space-y-2 md:space-y-0 md:space-x-2">
                            <div>
                                <label class=" font-bold">From:</label>
                            <input type="date" v-model="filter.from_date" class="input-field">
                        </div>

                        <div class="flex flex-col md:flex-row items-center space-y-2 md:space-y-0 md:space-x-2">
                            <label class=" font-bold">To:</label>
                            <input type="date"  v-model="filter.to_date"  class="input-field">
                        </div>

                        <button @click="resetFilters" class="reset-button text-white py-2 px-3 bg-[#38419D] rounded-full shadow border-gray-300 border hover:bg-[#3887BE] rounded ml-3">
                            Reset
                        </button>
                            </div>
                        <div>
                        <div class="py-3 px-4">
                        <div class="relative max-w-xs">
                            <label for="hs-table-search" class="sr-only">Search</label>
                            <input  type="search" v-model="search"  name="hs-table-search" id="hs-table-search" class="py-2 px-3 ps-9 block w-full border-gray-200 shadow-sm rounded-lg text-sm focus:z-10 focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none " placeholder="Search by appointment">
                            <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-3">
                                <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
        </div>
            <div class=" py-4 md:py-7 px-4 md:px-8 xl:px-10" :class="themeMode">
                <div class="sm:flex items-center justify-between">
                    <div class="flex items-center">
                        <button @click="openTab = 1" :class="{ 'bg-indigo-50 focus:bg-indigo-50 focus:ring-indigo-800 font-bold': openTab === 1 }" class="rounded-full focus:outline-none focus:ring-2  " href=" javascript:void(0)">
                            <div class="py-2 px-8   rounded-full" :class="{ 'text-indigo-700 font-bold': openTab === 1 }">
                                <p>All</p>
                            </div>
                        </button>

                    </div>
                    <!-- <Link :href="route('appointment.create')" class="focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600 mt-4 sm:mt-0 inline-flex items-start justify-start px-6 py-3 bg-indigo-700 hover:bg-indigo-600 focus:outline-none rounded">
                        <p class="text-sm font-medium leading-none text-white">Add Appointment</p>
                    </Link> -->
                </div>

                <div class="overflow-x-auto w-[1000px]">
                    <div  v-if="openTab === 1" class="mt-7">
                    <table class="whitespace-nowrap divide-y min-w-full">
                        <thead>
                            <tr>
                                <td>Client Name</td>
                                <td class="text-left">Date</td>
                                <td class="text-center">Post</td>
                                <td class="text-center">Stay Duration</td>
                                <td class="text-center">Person Quantity</td>
                                <td class="text-center">Price</td>
                                <td class="text-center">Total</td>
                                <td class="text-center">Status</td>
                                <td class="text-center" v-if="isAdmin">Action</td>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- <tr v-if="appointments.length === 0">
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-lg  text-gray-800 text-center">
                                        No appointment found.
                                </td>
                            </tr> -->
                            <tr tabindex="0" class="focus:outline-none h-16 border border-gray-100 rounded" v-for="app in appointments.data" :key="app.id">
                                <td>
                                    <div class="flex items-center pl-5">
                                        <p class="text-base font-medium leading-none  mr-2">{{ app.user.name }}</p>

                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="flex items-center">
                                        <p class="text-base font-medium leading-none  mr-2">
                                            {{ formattedDate(app.date)}}  at {{ formatTimeToAMPM(app.time) }}
                                        </p>

                                    </div>
                                </td>

                                <td class="text-center">
                                        <p class="text-sm leading-none ml-2">{{ app.post.name }}</p>
                                </td>
                                <td class="text-center">
                                        <p class="text-sm leading-none ml-2">{{ app.days_stay }} day(s).</p>
                                </td>
                                <td class="text-center">
                                        <p class="text-sm leading-none ml-2">{{ app.person_number }}</p>
                                </td>
                                <td class="text-center">
                                        <p class="text-sm leading-none ml-2"><i class="fas fa-peso-sign"></i>{{ app.post.price }}/night (2pax) (+200/person)</p>
                                </td>
                                <td class="text-center">
                                        <p class="text-sm leading-none ml-2"><i class="fas fa-peso-sign"></i>{{ app.post.price * app.days_stay + 200 * app.person_number - 400}}</p>
                                </td>
                                <td class="text-center">
                                    <div class="flex items-center justify-center">

                                        <p class="text-xs uppercase px-4 py-1 rounded-full border font-bold" :class="{
                                            'bg-blue-500 text-blue-200': app.status == 'Pending',
                                            'bg-red-500 text-red-200':app.status == 'Cancelled',
                                            'bg-green-500 text-green-200': app.status =='Accepted',
                                        }">{{ app.status }}</p>
                                    </div>
                                </td>
                                <td v-if="isAdmin">
                                    <div class="px-5 text-center">
                                        <div class="flex item-center justify-center">
                                            <div class="flex item-center justify-center" v-if="app.status == 'Pending'">

                                                <div class="w-4 mr-5 transform hover:text-green-500 hover:scale-110">

                                                    <Link :href="'/appointment/accept/' + app.id" method="post" class="btn" as="button" title="Accept Appointment">
                                                        <i class="fas fa-check text-green-800"></i>
                                                    </Link>

                                                </div>
                                                <div class="w-4 mr-2 transform hover:text-red-500 hover:scale-110">

                                                    <Link :href="'/appointment/cancel/' + app.id" method="post" class="btn " as="button" title="Cancel Appointment">
                                                        <i class="fas fa-xmark text-red-800"></i>
                                                    </Link>


                                                </div>
                                            </div>
                                             <p v-else class="text-xs text-center text-green-900 uppercase px-2 py-1 rounded-full w-50 font-bold first-letter"><i class="fas fa-check-double"></i> Done</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="h-3" v-if="appointments.data == 0">
                            <td colspan="8" class="text-center">
                                No records found
                            </td>
                             </tr>

                        </tbody>
                    </table>

                </div>
                </div>
            </div>
        </div>
    </Sidebar>


</template>
<style scoped>
.checkbox:checked + .check-icon {
    display: flex;
  }


#flash-success-message {
    animation: fadeOut 6s ease-in-out forwards;
}

.progress-bar {
    height: 5px;
    width: 100%;
    background-color: #4CAF50; /* Green color */
    animation: progressBar 3s linear;
}
#flash-error-message {
    animation: fadeOut 7s ease-in-out forwards;
}

.success .progress-bar {

    animation: progressBar 5s linear;
}
.error .progress-bar {
    background-color: #FF5733; /* Red color */
    animation: progressBar 5s linear;
}

@keyframes fadeOut {
    from {
        opacity: 1;
    }
    to {
        opacity: 0;
    }
}

@keyframes progressBar {
    0% {
        width: 100%;
    }
    100% {
        width: 0;
    }
}
</style>
