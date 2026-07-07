from fastapi import APIRouter

from app.api.v1 import cv, panel

api_router = APIRouter()
api_router.include_router(cv.router, prefix="/cv", tags=["CV"])
api_router.include_router(panel.router, prefix="/panel", tags=["Panel"])
