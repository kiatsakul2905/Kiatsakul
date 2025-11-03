from flask import Flask, request, jsonify
from flask_cors import CORS
import pymysql
from pythainlp.tokenize import word_tokenize

app = Flask(__name__)
CORS(app)  # อนุญาต CORS

# DB connection
def get_db_connection():
    return pymysql.connect(
        host='localhost',
        user="",
        password="",
        database="foodie_moodie",
        charset='utf8mb4',
        cursorclass=pymysql.cursors.DictCursor
    )

def get_keywords_from_db():
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT keyword, name FROM emotions")
    rows = cursor.fetchall()
    conn.close()

    happy = [r['keyword'] for r in rows if r['name']=='happy']
    sad = [r['keyword'] for r in rows if r['name']=='sad']
    return set([w.lower() for w in happy]), set([w.lower() for w in sad]), happy, sad

@app.route('/check_keywords', methods=['POST'])
def check_keywords():
    if not request.json or 'text' not in request.json:
        return jsonify({"error": "กรุณาส่งข้อความ JSON พร้อม key 'text'"}), 400

    text = request.json['text'].strip()
    if not text:
        return jsonify({"error": "ข้อความว่าง"}), 400

    keywords_group1_lower, keywords_group2_lower, all_happy, all_sad = get_keywords_from_db()
    words = [w.lower() for w in word_tokenize(text, engine='newmm')]
    print("🧩 การตัดคำ:", " / ".join(words))
    found_group1 = [w for w in words if w in keywords_group1_lower]
    found_group2 = [w for w in words if w in keywords_group2_lower]

    # ตัดสินใจอารมณ์
    if len(found_group1) == 0 and len(found_group2) == 0:
        message = "ไม่พบคำ"
    elif len(found_group1) > len(found_group2):
        message = "มีความสุข"
    elif len(found_group2) > len(found_group1):
        message = "มีความทุกข์"
    else:
        message = "เท่ากัน"

    return jsonify({
        "keywords_happy": found_group1,
        "keywords_sad": found_group2,
        "all_keywords": {"happy": all_happy, "sad": all_sad},
        "message": message
    })

if __name__ == "__main__":
    app.run(host='0.0.0.0', port=5000, debug=True)

