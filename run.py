import json
import os
import sys
import argparse

def calcular_energia(daily_kwh, price_kwh, solar_gen, solar_cost=0):
    monthly_usage = daily_kwh * 30
    monthly_cost_no_solar = monthly_usage * price_kwh
    
    monthly_solar_gen = solar_gen * 30
    remaining_energy = max(0, monthly_usage - monthly_solar_gen)
    monthly_cost_with_solar = remaining_energy * price_kwh
    
    monthly_savings = monthly_cost_no_solar - monthly_cost_with_solar
    annual_savings = monthly_savings * 12
    
    payback_years = solar_cost / annual_savings if solar_cost > 0 and annual_savings > 0 else 0
    payback_months = (payback_years - int(payback_years)) * 12
    
    return {
        "inputs": {
            "daily_kwh": daily_kwh,
            "price_kwh": price_kwh,
            "solar_gen": solar_gen,
            "solar_cost": solar_cost
        },
        "results": {
            "monthly_usage": monthly_usage,
            "monthly_cost_no_solar": monthly_cost_no_solar,
            "monthly_solar_gen": monthly_solar_gen,
            "remaining_energy": remaining_energy,
            "monthly_cost_with_solar": monthly_cost_with_solar,
            "monthly_savings": monthly_savings,
            "annual_savings": annual_savings,
            "payback_years": int(payback_years),
            "payback_months": int(payback_months)
        }
    }

def main():
    # Verificar se foi chamado com argumentos para JSON
    parser = argparse.ArgumentParser(description='Calculadora de Energia Solar')
    parser.add_argument('--json', action='store_true', help='Output in JSON format')
    parser.add_argument('daily_kwh', type=float, nargs='?', help='Daily energy usage in kWh')
    parser.add_argument('price_kwh', type=float, nargs='?', help='Price per kWh')
    parser.add_argument('solar_gen', type=float, nargs='?', help='Solar generation per day')
    parser.add_argument('solar_cost', type=float, nargs='?', default=0, help='Solar installation cost')
    
    args = parser.parse_args()
    
    # Se solicitado para saída JSON com todos os parâmetros
    if args.json and args.daily_kwh is not None and args.price_kwh is not None and args.solar_gen is not None:
        resultado = calcular_energia(
            args.daily_kwh, 
            args.price_kwh, 
            args.solar_gen, 
            args.solar_cost
        )
        print(json.dumps(resultado))
        return
    
    # Interface interativa
    while True:
        os.system('cls' if os.name == 'nt' else 'clear')
        print("=" * 80)
        print("                    HOUSEHOLD ENERGY & SOLAR SAVINGS CALCULATOR")
        print("=" * 80)
        print("\nThis tool helps you calculate potential savings from solar energy adoption.")
        print("Enter your household energy information to get started.\n")
        
        daily_kwh = float(input("Average daily energy usage (kWh): "))
        price_kwh = float(input("Price per kWh ($): "))
        solar_gen = float(input("Estimated solar panel generation (kWh/day): "))
        solar_cost_input = input("Solar installation cost ($) [optional, press Enter to skip]: ")
        solar_cost = float(solar_cost_input) if solar_cost_input else 0
        
        resultado = calcular_energia(daily_kwh, price_kwh, solar_gen, solar_cost)
        
        print("\n" + "-" * 60)
        print("                    RESULTS SUMMARY")
        print("-" * 60)
        print("\nYOUR INPUTS:")
        print(f"• Daily energy usage: {resultado['inputs']['daily_kwh']:.2f} kWh")
        print(f"• Electricity price: ${resultado['inputs']['price_kwh']:.4f} per kWh")
        print(f"• Solar generation: {resultado['inputs']['solar_gen']:.2f} kWh/day")
        print(f"• Installation cost: ${resultado['inputs']['solar_cost']:.2f}\n")
        
        print("MONTHLY ENERGY PROFILE:")
        print(f"• Energy consumption: {resultado['results']['monthly_usage']:.2f} kWh")
        print(f"• Cost without solar: ${resultado['results']['monthly_cost_no_solar']:.2f}")
        print(f"• Total solar generation: {resultado['results']['monthly_solar_gen']:.2f} kWh")
        print(f"• Remaining energy needed: {resultado['results']['remaining_energy']:.2f} kWh")
        print(f"• Cost with solar: ${resultado['results']['monthly_cost_with_solar']:.2f}\n")
        
        print("SAVINGS:")
        print(f"• Monthly savings: ${resultado['results']['monthly_savings']:.2f}")
        print(f"• Annual savings: ${resultado['results']['annual_savings']:.2f}\n")
        
        print("PAYBACK PERIOD:")
        print(f"• Estimated payback time: {resultado['results']['payback_years']} years and {resultado['results']['payback_months']} months")
        print("\n" + "-" * 60)
        
        if input("\nWould you like to perform another calculation? (y/n): ").lower() != 'y':
            break

if __name__ == "__main__":
    main()