from fastapi import APIRouter

from app.api.v1 import cv

api_router = APIRouter()
api_router.include_router(cv.router, prefix="/cv", tags=["CV"])
