<template>
    <AuthenticatedLayout :title="props.title">
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs"/>

        <section class="space-y-4 bg-white dark:bg-slate-800 shadow rounded-t-lg">
            <div class="mt-0 p-4">
                <div class="block-header mb-5 flex flex-col md:flex-row justify-between items-start md:items-center pb-3 border-b border-gray-300 dark:border-neutral-600 gap-4">
                    <h1 class="text-xl md:text-2xl font-bold">{{ contract.title }}</h1>
                    <div class="actions flex flex-wrap gap-2">

                        <Link
                            v-if="contract.status === 3 && contract.user_id === authUser.id"
                            :href="route('contract.upload-scan', { contract: contract.id })"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition"
                            v-tooltip="lang().label.upload_scan"
                        >
                            <i class="pi pi-upload mr-2"></i>
                            {{ lang().label.upload_scan }}
                        </Link>

                        <Button
                            v-if="userApproval"
                            type="button"
                            icon="pi pi-check-circle"
                            :label="lang().button.approve"
                            severity="success"
                            class="p-button-sm dark:text-white"
                            @click="(data.approveOpen = true), (data.contract = contract)"
                        />

                        <Button
                            v-if="contract.status === 1 && contract.user_id === authUser.id"
                            type="button"
                            icon="pi pi-send"
                            :label="lang().tooltip.send_for_approval"
                            severity="info"
                            class="p-button-sm dark:text-white"
                            @click="confirmDialogRef.open(contract)"
                        />

                        <SendApproval ref="confirmDialogRef"
                                      v-if="contract.status === 1"
                        />

                        <Button
                            v-if="userApproval"
                            type="button"
                            icon="pi pi-times"
                            severity="danger"
                            :label="lang().label.cancel_approval"
                            class="p-button-sm bg-yellow-500 text-white dark:text-white"
                            @click="(data.cancelApproval = true), (data.contract = contract)"
                        />

                        <DeleteUser
                            :show="data.deleteUserOpen"
                            @close="data.deleteUserOpen = false"
                            :contract="data.contract"
                            :user="data.selectedUser"
                            :title="props.title"
                        />

                        <EditUser
                            :show="data.editUserOpen"
                            @close="data.editUserOpen = false"
                            :contract="props.contract"
                            :users="props.users"
                            :approvals="props.approvals"
                            :title="props.title"
                        />

                        <Approve
                            v-if="userApproval"
                            :show="can(['approve contract']) && data.approveOpen"
                            @close="data.approveOpen = false"
                            :contract="data.contract"
                            :title="props.title"
                        />

                        <CancelApproval
                            v-if="userApproval"
                            :show="can(['approve contract']) && data.cancelApproval"
                            @close="data.cancelApproval = false"
                            :contract="data.contract"
                            :title="props.title"
                        />

                        <EditLink
                            v-if="contract.user_id === authUser.id && contract.status !== 3"
                            :href="route('contract.edit', { contract: contract.id })"
                            class="px-4 py-2 rounded-md"
                            v-tooltip="lang().tooltip.edit"
                        >
                            {{ lang().tooltip.edit }}
                        </EditLink>

                        <DangerButton
                            type="button"
                            @click="(data.deleteOpen = true), (data.contract = contract)"
                            class="px-4 py-2 rounded-md"
                            v-tooltip="lang().tooltip.delete"
                            v-show="can(['delete contract'])"
                            v-if="props.contract.status ===1 && contract.user_id === authUser.id"
                        >
                            {{ lang().tooltip.delete }}
                            <TrashIcon class="w-5 h-5" />
                        </DangerButton>

                        <Delete
                            :show="data.deleteOpen"
                            @close="data.deleteOpen = false"
                            :contract="data.contract"
                            :title="props.title"
                        />

                        <ApprovalHistory
                            :show="data.showHistory"
                            :approval="data.historyApproval"
                            :all-approvals="approvals"
                            @close="data.showHistory = false"
                        />
                    </div>
                </div>

                <div class="p-2 sm:p-4 xs:p-3 bg-gray-100 dark:bg-neutral-800 rounded-lg shadow-md mb-4">
                    <div class=" flex flex-wrap gap-2 items-center mb-3 justify-between">
                        <h2 class="text-lg font-bold">{{ lang().label.approval_status }}</h2>
                        <Button
                            icon="pi pi-user-plus"
                            :label="lang().button.edit"
                            severity="info"
                            class="p-button-sm dark:text-white"
                            :disabled="contract.status === 3"
                            @click="data.editUserOpen = true"
                            v-if="contract.user_id === authUser.id && contract.status !== 3"
                        />
                    </div>

                    <div class="space-y-2">
                        <Message
                            v-if="contract.transaction_type === 2 ? (activeApprovals.length < 1 && contract.user_id === authUser.id) : (activeApprovals.length < 2 && contract.user_id === authUser.id)"
                            severity="warn"
                            :closable="false"
                            class="mb-2"
                        >
                            {{ contract.transaction_type === 2 ? 'Для одобрения контракта необходим минимум 1 утвердитель.' : lang().label.min_approvers_warning }}
                        </Message>

                        <Message
                            v-if="contract.status === 2 && activeApprovals.length > 1"
                            severity="info"
                            :closable="false"
                            class="mb-2"
                        >
                            <i class="pi pi-info-circle mr-2"></i>
                            {{ lang().approval.sequential_approval_info }}
                        </Message>

                        <div v-if="activeApprovals.length" class="flex flex-col gap-4">
                            <Card
                                v-for="approval in activeApprovals"
                                :key="approval.user_id"
                                class="shadow-md"
                                :class="{
                                    'border-2 border-green-400': approval.user_id === authUser.id && approval.approved === 2 && can_approve,
                                    'border-2 border-orange-300': approval.user_id === authUser.id && approval.approved === 2 && !can_approve,
                                }"
                            >
                                <template #content>
                                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                        <div class="flex-1">
                                            <!-- Header with avatar and order badge -->
                                            <div class="flex items-center gap-3 mb-3">
                                                <div class="relative">
                                                    <img
                                                        :src="approval.user_avatar"
                                                        :alt="approval.user_name"
                                                        class="w-14 h-14 rounded-full object-cover border-2 border-gray-200 dark:border-gray-600"
                                                        @error="(e) => e.target.src = '/images/no_image.png'"
                                                    />
                                                    <span class="absolute -bottom-1 -right-1 text-sm font-bold bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center border-2 border-white dark:border-gray-800">
                                                        {{ approval.approval_order }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h3 class="text-lg font-semibold">{{ approval.user_name }}</h3>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ approval.department_name }}</p>
                                                </div>
                                            </div>

                                            <!-- Status -->
                                            <div class="mb-3">
                                                <span
                                                    class="inline-block px-3 py-1 rounded-full text-sm font-semibold"
                                                    :class="{
                                                        'bg-green-100 text-green-800': approval.approved === 3,
                                                        'bg-red-100 text-red-800': approval.approved === -1,
                                                        'bg-gray-200 text-gray-700': approval.approved === -2,
                                                        'bg-yellow-100 text-yellow-800': approval.approved === 2,
                                                        'bg-blue-100 text-blue-800': approval.approved === 1
                                                    }"
                                                >
                                                    {{
                                                        contract.status === 1 && approval.approved === 1
                                                            ? lang().status.not_sent
                                                            : approval.approved === 3
                                                                ? lang().status.approved
                                                                : approval.approved === -1
                                                                    ? lang().status.rejected
                                                                    : approval.approved === -2
                                                                        ? lang().status.invalidated
                                                                        : lang().status.in_progress
                                                    }}
                                                </span>
                                                <span
                                                    v-if="approval.approved_at"
                                                    class="text-sm text-gray-500 ml-2"
                                                >
                                                    ({{ approval.approved_at }})
                                                </span>
                                            </div>

                                            <!-- Blocking message for current user -->
                                            <Message
                                                v-if="approval.user_id === authUser.id && block_info?.blocked && approval.approved === 2"
                                                severity="warn"
                                                :closable="false"
                                                class="mb-2"
                                            >
                                                <i class="pi pi-lock mr-2"></i>
                                                {{ block_info.message }}
                                            </Message>

                                            <!-- Can approve now indicator -->
                                            <Message
                                                v-if="approval.user_id === authUser.id && can_approve && approval.approved === 2"
                                                severity="success"
                                                :closable="false"
                                                class="mb-2"
                                            >
                                                <i class="pi pi-check-circle mr-2"></i>
                                                {{ lang().approval.can_approve_now }}
                                            </Message>

                                            <p v-if="approval.reason" class="text-base">
                                                <span class="font-semibold text-gray-800 dark:text-gray-200">
                                                    {{ lang().label.comment }}:
                                                </span>
                                                <span class="text-gray-700 dark:text-gray-300">
                                                    {{ approval.reason }}
                                                </span>
                                            </p>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <Button
                                                icon="pi pi-eye"
                                                severity="info"
                                                class="p-button-sm"
                                                @click="openApprovalHistory(approval)"
                                                v-tooltip.bottom="lang().tooltip.view_details"
                                            />
                                            <Button
                                                v-if="contract.user_id === authUser.id && contract.status !== 3"
                                                type="button"
                                                icon="pi pi-trash"
                                                severity="danger"
                                                class="p-button-sm"
                                                :disabled="approval.approved === 3"
                                                @click="() => confirmRemoveApprover(approval)"
                                            />
                                        </div>
                                    </div>
                                </template>
                            </Card>
                        </div>

                    </div>

                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-300 dark:border-neutral-700 divide-y divide-gray-200 dark:divide-neutral-700">
                        <tbody>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">ID</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ contract.id }}</td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.contract_number }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ contract.contract_number }}</td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.title }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ contract.title }}</td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.transaction_type }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                                <span
                                    class="inline-block px-3 py-1 rounded-full text-sm font-semibold"
                                    :class="{
                                        'bg-green-100 text-green-800': contract.transaction_type === 2,
                                        'bg-blue-100 text-blue-800': contract.transaction_type === 1
                                    }"
                                >
                                    {{ transaction_types.find(t => t.id === contract.transaction_type)?.label || lang().label.undefined }}
                                </span>
                            </td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.project_id }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                                <span v-if="props.project">
                                        {{ props.project.title ?? lang().label.undefined }}
                                </span>
                                <span v-else>
                                    {{ lang().label.undefined }}
                                </span>
                            </td>
                        </tr>
                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                                {{ lang().label.application_id }}
                            </td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                                <Button v-if="application" @click="showModal = true" label="Help">
                                    {{ lang().label.show_application }}
                                </Button>
                            </td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.user_id }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ contract.user.name }}</td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.status }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                                <span
                                    class="inline-block px-3 py-1 rounded-full text-sm font-semibold"
                                    :class="getStatusClass(contract.status)"
                                >
                                      {{ getStatusLabel(contract.status) }}
                                </span>
                            </td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.currency_id }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                                {{ contract.currency.name }}
                            </td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.deadline }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                                {{ contract.deadline }}
                            </td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.contract_sum }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ formatNumber(contract.budget_sum) }} {{ contract.currency?.short_name || '' }}</td>
                        </tr>
                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.files }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                                <div v-if="props.files.length > 0">
                                    <ul class="list-none p-0 flex flex-col gap-2">
                                        <li v-for="(file, index) in props.files" :key="index">
                                            <Button
                                                icon="pi pi-eye"
                                                :label="file.name"
                                                @click="openFileViewer(file)"
                                                size="small"
                                                outlined
                                                severity="info"
                                                class="text-left"
                                            />
                                        </li>
                                    </ul>
                                </div>
                                <div v-else>
                                    {{ lang().label.no_files }}
                                </div>
                            </td>
                        </tr>

                        <tr
                            v-if="contract.status === 3 ?? scans.length > 0"
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.scans }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                                <div v-if="props.scans.length > 0">
                                    <ul class="list-none p-0 flex flex-col gap-2">
                                        <li v-for="(file, index) in props.scans" :key="index">
                                            <Button
                                                icon="pi pi-eye"
                                                :label="file.name"
                                                @click="openFileViewer(file)"
                                                size="small"
                                                outlined
                                                severity="success"
                                                class="text-left"
                                            />
                                        </li>
                                    </ul>
                                </div>
                                <div v-else>
                                    {{ lang().label.no_files }}
                                </div>
                            </td>
                        </tr>

                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.created }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ contract.created_at }}</td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.updated }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ contract.updated_at }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>


        <Dialog v-model:visible="showModal" modal :header="lang().label.application_details" class="w-[90vw] sm:w-2/3 md:w-1/2 lg:w-2/3">
            <div class="space-y-2 my-4">
                <div v-if="uniqueApprovals.length" class="flex flex-col gap-4">
                    <Card
                        v-for="approval in uniqueApprovals" :key="approval.user_id"
                        class="shadow-md"
                    >
                        <template #content>
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                <div>
                                    <h3 class="text-lg font-semibold mb-3">👤 {{ approval.user_name }}</h3>
                                    <div class="mb-3">
                                            <span
                                                class="inline-block px-3 py-1 rounded-full text-sm font-semibold"
                                                :class="{
                                              'bg-green-100 text-green-800': approval.approved === 3,
                                              'bg-red-100 text-red-800': approval.approved === -1,
                                              'bg-gray-200 text-gray-700': approval.approved === -2,
                                              'bg-yellow-100 text-yellow-800': approval.approved === 2,
                                              'bg-blue-100 text-blue-800': approval.approved === 1
                                            }"
                                            >
                                              {{
                                                    application.status_id === 1 && approval.approved === 1
                                                        ? lang().status.not_sent
                                                        : approval.approved === 3
                                                            ? lang().status.approved
                                                            : approval.approved === -1
                                                                ? lang().status.rejected
                                                                : approval.approved === -2
                                                                    ? lang().status.invalidated
                                                                    : lang().status.in_progress
                                                }}

                                        </span>
                                        <span
                                            v-if="approval.approved_at"
                                            class="text-sm text-gray-500 ml-2"
                                        >
                                                ({{ approval.approved_at }})
                                            </span>
                                    </div>
                                    <p v-if="approval.reason" class="text-base">
                                                <span class="font-semibold text-gray-800 dark:text-gray-200">
                                                    {{ lang().label.comment }}:
                                                </span>
                                        <span class="text-gray-700 dark:text-gray-300">
                                                    {{ approval.reason }}
                                                </span>
                                    </p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <Button
                                        icon="pi pi-eye"
                                        severity="info"
                                        class="p-button-sm"
                                        @click="openApplicationHistory(approval)"
                                        v-tooltip.bottom="lang().tooltip.view_details"
                                    />
                                </div>
                            </div>
                        </template>

                    </Card>

                </div>
            </div>

            <!-- ApplicationHistory moved outside the loop to prevent duplicates -->
            <ApplicationHistory
                :show="data.showApplicationHistory"
                :approval="data.applicationHistoryApproval"
                :all-approvals="application_approvals"
                @close="data.showApplicationHistory = false"
            />

            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-300 dark:border-neutral-700 divide-y divide-gray-200 dark:divide-neutral-700">
                    <tbody>
                    <tr>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600 font-bold">{{ lang().label.id }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ props.application?.id ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600 font-bold">{{ lang().label.title }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ props.application?.title ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600 font-bold">{{ lang().label.type }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                            {{ props.types.find(t => t.id === props.application?.type)?.label ?? '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600 font-bold">{{ lang().label.project }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                            {{ props.project?.title ?? 'Не указан' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600 font-bold">{{ lang().label.user }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                            {{ props.application?.user?.name ?? 'Не указан' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600 font-bold">{{ lang().label.status }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                            <span
                                class="inline-block px-3 py-1 rounded-full text-sm font-semibold"
                                :class="getStatusClass(props.application.status_id)"
                            >
                                      {{ getStatusLabel(props.application.status_id) }}
                            </span>

                        </td>
                    </tr>
                    <tr>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600 font-bold">{{ lang().label.created }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                            {{ props.application?.created_at ?? '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600 font-bold">{{ lang().label.updated }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                            {{ props.application?.updated_at ?? '-' }}
                        </td>
                    </tr>

                    <tr>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600 font-bold">{{ lang().label.files }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                            <ul v-if="props.application?.media?.length > 0" class="list-none space-y-2">
                                <li v-for="(file, index) in props.application.media" :key="index">
                                    <Button
                                        icon="pi pi-eye"
                                        :label="file.name"
                                        @click="openFileViewer(file)"
                                        size="small"
                                        outlined
                                        severity="info"
                                        class="text-left"
                                    />
                                </li>
                            </ul>
                            <p v-else class="text-gray-500">{{ lang().label.no_files }}</p>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </Dialog>

        <!-- File Viewer Modal -->
        <FileViewer
            :file="data.selectedFile"
            v-model:visible="data.fileViewerVisible"
        />
    </AuthenticatedLayout>
</template>

<script setup>
import {Link, usePage} from '@inertiajs/vue3';
import {defineProps, defineEmits, reactive, computed} from 'vue';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {TrashIcon} from "@heroicons/vue/24/solid";
import EditLink from "@/Components/EditLink.vue";
import Approve from "@/Pages/Contract/Approve.vue";
import Delete from "@/Pages/Contract/Delete.vue";
import DangerButton from "@/Components/DangerButton.vue";
import DeleteUser from "@/Pages/Contract/DeleteUser.vue";
import Button from "primevue/button";
import EditUser from "@/Pages/Contract/EditUser.vue";
import { ref } from "vue";
import Dialog from "primevue/dialog";
import SendApproval from "@/Pages/Contract/SendApproval.vue";
import CancelApproval from "@/Pages/Contract/CancelApproval.vue";
import ApprovalHistory from "@/Pages/Contract/ApprovalHistory.vue";
import Message from "primevue/message";
import {Card} from "primevue";
import ApplicationHistory from "@/Pages/Contract/ApplicationHistory.vue";
import FileViewer from "@/Components/FileViewer.vue";

const showModal = ref(false);

const authUser = usePage().props.auth.user;

const props = defineProps({
    contract: Object,
    can_approve: Boolean,
    application: Object,
    title: String,
    breadcrumbs: Object,
    application_approvals: Array,
    statuses: Array,
    transaction_types: Array,
    project: Object,
    users: Array,
    approvals: Object,
    types: Object,
    files: Array,
    scans: Array,
    block_info: Object,
});

const data = reactive({
    deleteOpen: false,
    editUserOpen: false,
    project: null,
    approveOpen: false,
    cancelApproval: false,
    deleteUserOpen: false,
    selectedApprovers: computed(() => props.approvals.map(a => a.user_id)),
    showHistory: false,
    historyApproval: null,
    showApplicationHistory: false,
    applicationHistoryApproval: null,
    fileViewerVisible: false,
    selectedFile: null,
});

const emit = defineEmits(["close"]);

const confirmRemoveApprover = (user) => {
    data.selectedUser = user;
    data.contract = props.contract;
    data.deleteUserOpen = true;
};

const getStatusLabel = (statusId) => {
    const status = props.statuses.find(s => s.id === statusId);
    return status ? status.label : '';
};
const formatNumber = (amount) => {
    if (!amount) return '-';
    const formattedAmount = new Intl.NumberFormat('ru-RU', {
        style: 'decimal',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);

    return formattedAmount;
};

const getStatusClass = (statusId) => {
    const map = {
        1: 'bg-blue-100 text-blue-800',
        2: 'bg-yellow-100 text-yellow-800',
        3: 'bg-green-100 text-green-800',
        '-1': 'bg-red-100 text-red-800',
        '-2': 'bg-gray-200 text-gray-700',
    };

    return map[statusId] || 'bg-slate-200 text-slate-800';
};

const activeApprovals = computed(() => {
    const uniqueByUser = {};
    props.approvals
        .filter(a => a.approved !== -2)
        .forEach(a => {
            if (!uniqueByUser[a.user_id]) {
                uniqueByUser[a.user_id] = a;
            }
        });
    return Object.values(uniqueByUser);
});


const userApproval = computed(() =>
    activeApprovals.value.find(a => a.user_id === authUser.id && a.approved === 2)
);

const confirmDialogRef = ref();

const openApprovalHistory = (approval) => {
    data.historyApproval = approval;
    data.showHistory = true;
};

const openApplicationHistory = (approval) => {
    data.applicationHistoryApproval = approval;
    data.showApplicationHistory = true;
};

const openFileViewer = (file) => {
    data.selectedFile = file;
    data.fileViewerVisible = true;
};


const uniqueApprovals = computed(() => {
    const sorted = [...props.application_approvals].sort(
        (a, b) => new Date(a.updated_at) - new Date(b.updated_at)
    );
    const map = {};
    sorted.forEach(a => {
        map[a.user_id] = a;
    });
    return Object.values(map);
});


</script>
