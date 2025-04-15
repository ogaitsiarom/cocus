import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from '@/utils/api'

/**
 * Represents a note with an identifier, title, content, and timestamps for creation and last update.
 */
export interface Note {
  id: number
  title: string
  content: string
  createdAt: string
  updatedAt: string
}

/**
 * Pinia Store managing notes functionality and state within the application.
 */
export const useNotesStore = defineStore('notes', () => {
  const notes = ref<Note[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  /**
   * Fetches notes from the API and updates the state with the retrieved data, handles loading state, and captures any errors encountered during the process.
   *
   * @return {Promise<void>} A promise that resolves after the notes are fetched and the state is updated.
   */
  async function fetchNotes(): Promise<void> {
    isLoading.value = true
    error.value = null

    try {
      notes.value = await api.get<Note[]>('api/notes')
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Unknown error occurred'
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Adds a new note with the specified title and content by making an API request.
   *
   * @param {string} title - The title of the note to be added.
   * @param {string} content - The content of the note to be added.
   * @return {Promise<Note>} A promise that resolves to the newly added note object.
   * @throws {Error} Throws an error if the API request fails.
   */
  async function addNote(title: string, content: string): Promise<Note> {
    isLoading.value = true
    error.value = null

    try {
      const newNote = await api.post<Note>('api/note', { title, content })
      notes.value.push(newNote)
      return newNote
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Unknown error occurred'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Updates an existing note with the given title and content.
   *
   * @param {number} id - The unique identifier of the note to be updated.
   * @param {string} title - The new title for the note.
   * @param {string} content - The new content for the note.
   * @return {Promise<Note>} A promise that resolves with the updated note object.
   * @throws {Error} Throws an error if the update operation fails.
   */
  async function updateNote(id: number, title: string, content: string): Promise<Note> {
    isLoading.value = true
    error.value = null

    try {
      const updatedNote = await api.put<Note>(`api/note/${id}`, { title, content })

      const index = notes.value.findIndex((n) => n.id === id)
      if (index !== -1) {
        notes.value[index] = updatedNote
      }

      return updatedNote
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Unknown error occurred'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Deletes a note by its ID.
   *
   * @param {number} id - The ID of the note to be deleted.
   * @return {Promise<boolean>} A promise that resolves to true if the note is successfully deleted, or throws an error if the operation fails.
   */
  async function deleteNote(id: number): Promise<boolean> {
    isLoading.value = true
    error.value = null

    try {
      await api.delete(`api/note/${id}`)

      notes.value = notes.value.filter((note) => note.id !== id)
      return true
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Unknown error occurred'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Fetches a note by its ID. If the note is already loaded, it is returned from the cache (list). Otherwise, it fetches the note from the API.
   *
   * @param {number} id - The unique identifier of the note to retrieve.
   * @return {Promise<Note>} A promise that resolves to the note object or rejects with an error.
   */
  async function getNote(id: number): Promise<Note> {
    const existingNote = notes.value.find((note) => note.id === id)
    if (existingNote) {
      return existingNote
    }

    isLoading.value = true
    error.value = null

    try {
      return await api.get<Note>(`api/note/${id}`)
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Unknown error occurred'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  function clearNotes() {
    notes.value = []
  }

  return {
    notes,
    isLoading,
    error,
    fetchNotes,
    addNote,
    updateNote,
    deleteNote,
    getNote,
    clearNotes,
  }
})
