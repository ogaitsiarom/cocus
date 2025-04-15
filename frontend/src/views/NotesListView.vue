<template>
  <div class="notes-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1>My Notes</h1>
      <b-button variant="danger" @click="handleLogout">
        <i class="bi bi-box-arrow-right"></i> Logout
      </b-button>
    </div>

    <div class="d-flex justify-content-between mb-3">
      <b-button variant="success" @click="createNewNote" class="new-note-btn">
        <i class="bi bi-plus-circle"></i> New Note
      </b-button>
      <b-button variant="outline-primary" @click="refreshNotes" :disabled="notesStore.isLoading">
        <i class="bi bi-arrow-clockwise"></i> Refresh
      </b-button>
    </div>

    <div v-if="notesStore.isLoading" class="text-center my-5">
      <b-spinner label="Loading..."></b-spinner>
      <p class="mt-2">Loading notes...</p>
    </div>

    <div v-else-if="notesStore.error" class="alert alert-danger">
      <i class="bi bi-exclamation-triangle"></i>
      Error loading notes: {{ notesStore.error }}
      <div class="mt-2">
        <b-button size="sm" variant="outline-danger" @click="refreshNotes"> Try Again</b-button>
      </div>
    </div>

    <b-list-group v-else>
      <b-list-group-item
        v-for="note in notesStore.notes"
        :key="note.id"
        class="d-flex justify-content-between align-items-center"
        button
        @click="openNote(note.id)"
      >
        <div>
          <h5 class="mb-1">{{ note.title }}</h5>
          <small>{{ formatDate(note.updatedAt) }}</small>
        </div>
        <div>
          <b-button size="sm" variant="danger" @click.stop="confirmDeleteNote(note.id, note.title)">
            Delete
          </b-button>
        </div>
      </b-list-group-item>
    </b-list-group>

    <div
      v-if="!notesStore.isLoading && !notesStore.error && notesStore.notes.length === 0"
      class="text-center mt-5"
    >
      <p>You don't have any notes yet. Create your first note!</p>
      <b-button variant="primary" @click="createNewNote" class="new-note-btn"> Create Note</b-button>
    </div>

    <b-modal
      v-model="showDeleteModal"
      title="Confirm Delete"
      @ok="handleDeleteConfirmed"
      ok-variant="danger"
      ok-title="Delete"
      cancel-title="Cancel"
    >
      <p>
        Are you sure you want to delete "<strong>{{ noteToDelete.title }}</strong
        >"?
      </p>
      <p class="text-danger">This action cannot be undone.</p>
    </b-modal>

    <b-modal
      v-model="showLogoutModal"
      title="Confirm Logout"
      @ok="confirmLogout"
      ok-variant="primary"
      ok-title="Logout"
      cancel-title="Cancel"
    >
      <p>Are you sure you want to logout?</p>
      <p>Any unsaved changes will be lost.</p>
    </b-modal>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useNotesStore } from '@/stores/notes'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const notesStore = useNotesStore()
const authStore = useAuthStore()
const showDeleteModal = ref(false)
const showLogoutModal = ref(false)
const noteToDelete = ref<{ id: number; title: string }>({ id: 0, title: '' })

/**
 * Formats a date string into a human-readable format
 *
 * @param {string} dateStr - The date string to be formatted.
 * @returns {string} The formatted date string or the original input string if parsing fails.
 */
const formatDate = (dateStr: string): string => {
  try {
    const date = new Date(dateStr)
    return new Intl.DateTimeFormat('en-GB', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    }).format(date)
  } catch (e) {
    return dateStr
  }
}

/**
 * A function that navigates to the specified note page based on its ID.
 *
 * @function
 * @param {number} id - The unique identifier of the note to open.
 */
const openNote = (id: number) => {
  router.push(`/notes/${id}`)
}

/**
 * Navigates the user to the 'create new note' page.
 */
const createNewNote = () => {
  router.push('/notes/new')
}

/**
 * Handles the confirmation process for deleting a note.
 * Sets the note to be deleted and triggers the display of the delete confirmation modal.
 *
 * @param {number} id - The unique identifier of the note to be deleted.
 * @param {string} title - The title of the note to be deleted.
 */
const confirmDeleteNote = (id: number, title: string) => {
  noteToDelete.value = { id, title }
  showDeleteModal.value = true
}

/**
 * Handles the deletion of a note after confirmation is received.
 *
 * @async
 * @function
 */
const handleDeleteConfirmed = async () => {
  try {
    await notesStore.deleteNote(noteToDelete.value.id)
  } catch (error) {
    console.error('Failed to delete note:', error)
  }
}

/**
 * Asynchronously refreshes the notes by invoking the fetchNotes method from the notesStore.
 */
const refreshNotes = async () => {
  await notesStore.fetchNotes()
}

/**
 * Function that handles the logout process by triggering the display of a logout modal.
 */
const handleLogout = () => {
  showLogoutModal.value = true
}

/**
 * Handles the logout process for the user.
 */
const confirmLogout = async () => {
  try {
    await authStore.logout()
    notesStore.clearNotes()
    await router.push('/login')
  } catch (error) {
    console.error('Error during logout:', error)
  }
}

onMounted(async () => {
  if (notesStore.notes.length === 0) {
    await notesStore.fetchNotes()
  }
})
</script>

<style scoped>
.notes-container {
  width: 800px;
  margin: 0 auto;
  padding: 20px;
}

.new-note-btn {
  background-color: #ff971e;
  border-color: #ff971e;
}
</style>
