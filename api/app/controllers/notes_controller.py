from flask import Blueprint, jsonify, request
from app.middleware.jwt_middleware import jwt_required
from app.services.notes_service import NoteService

notes_bp = Blueprint('note', __name__, url_prefix='/api')

@notes_bp.route('/note/<int:note_id>', methods=['GET'])
@jwt_required
def get_note(note_id):
    notes = NoteService.get_note(note_id, request.user)
    return jsonify([{'id': n.id, 'title': n.title, 'content': n.content} for n in notes])

@notes_bp.route('/notes', methods=['GET'])
@jwt_required
def get_notes():
    notes = NoteService.get_all_notes(request.user)
    return jsonify([{'id': n.id, 'title': n.title, 'content': n.content} for n in notes])


@notes_bp.route('/note/', methods=['POST'])
@jwt_required
def create_note():
    data = request.json
    note = NoteService.create_note(data['title'], data['content'], request.user)
    return jsonify({'id': note.id, 'title': note.title, 'content': note.content}), 201


@notes_bp.route('/note/<int:note_id>', methods=['PUT'])
@jwt_required
def update_note(note_id):
    data = request.json
    note = NoteService.update_note(note_id, data, request.user)
    return jsonify({'id': note.id, 'title': note.title, 'content': note.content})


@notes_bp.route('/note/<int:note_id>', methods=['DELETE'])
@jwt_required
def delete_note(note_id):
    NoteService.delete_note(note_id, request.user)
    return jsonify({"message": "Note deleted"})
