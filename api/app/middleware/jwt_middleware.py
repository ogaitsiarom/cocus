import os

import jwt
from flask import request, abort
from functools import wraps

from app.models import User

key_path = os.path.join(os.path.dirname(__file__), '../keys/public.pem')
with open(key_path, 'rb') as pub_file:
    public_key = pub_file.read()


def jwt_required(f):
    @wraps(f)
    def decorator(*args, **kwargs):

        token = request.headers.get('Authorization', '').replace('Bearer ', '')
        if not token:
            abort(401, 'Missing token!')

        try:
            payload = jwt.decode(token, public_key, algorithms=['RS256'])
            username = payload.get('username')
            request.user = User.query.filter_by(username=username).first_or_404()
        except jwt.InvalidTokenError as e:
            abort(401, f'Invalid token: {str(e)}')

        return f(*args, **kwargs)

    return decorator
