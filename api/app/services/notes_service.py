from sqlalchemy import and_

from app import db
from app.models import User
from app.models.note import Note


class NoteService:

    @staticmethod
    def get_note(note_id: int, user: User):
        return Note.query.filter(and_(
            Note.id == note_id,
            Note.user_id == user.id
        )).first_or_404()

    @staticmethod
    def get_all_notes(user: User):
        return Note.query.filter_by(user_id=user.id).all()

    @staticmethod
    def create_note(title, content, user):
        note = Note(title=title, content=content, user_id=user.id)
        db.session.add(note)
        db.session.commit()
        return note

    @staticmethod
    def update_note(note_id, data, user):
        note = Note.query.filter_by(id=note_id, user_id=user.id).first_or_404()
        note.title = data.get('title', note.title)
        note.content = data.get('content', note.content)
        db.session.commit()
        return note

    @staticmethod
    def delete_note(note_id, user):
        note = Note.query.filter_by(id=note_id, user_id=user.id).first_or_404()
        db.session.delete(note)
        db.session.commit()
        return True
