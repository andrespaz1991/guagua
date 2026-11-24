import sys
assert sys.argv[1] == 'transcribe'
assert '--input' in sys.argv and '--output' in sys.argv
print('BAT: argumentos correctos, sin elevacion ni pausa, codigo de salida 7 de prueba.')
sys.exit(7)
